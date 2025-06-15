<?php
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SecureServer;
use React\Socket\Server;

class ChatServer implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->db = Database::getInstance()->getConnection();
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        $data = json_decode($msg);

        switch ($data->type) {
            case 'auth':
                $this->userConnections[$data->user_id] = $from;
                $this->updateOnlineStatus($data->user_id, true);
                break;

            case 'message':
                $this->handleNewMessage($from, $data);
                break;

            case 'typing':
                $this->broadcastToConversation($data->conversation_id, $msg, $from);
                break;

            case 'call_request':
            case 'call_answer':
            case 'call_ice_candidate':
            case 'call_end':
                $this->handleCallSignaling($data);
                break;
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Find and remove user connection
        $userId = array_search($conn, $this->userConnections);
        if ($userId !== false) {
            unset($this->userConnections[$userId]);
            $this->updateOnlineStatus($userId, false);
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function broadcastToConversation($conversationId, $message, $except = null) {
        // Get all participants in the conversation
        $stmt = $this->db->prepare("
            SELECT user_id 
            FROM chat_participants 
            WHERE conversation_id = ? AND is_active = 1
        ");
        $stmt->execute([$conversationId]);
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (isset($this->userConnections[$row['user_id']])) {
                $conn = $this->userConnections[$row['user_id']];
                if ($conn !== $except) {
                    $conn->send($message);
                }
            }
        }
    }

    protected function handleNewMessage($from, $data) {
        // Get message details
        $stmt = $this->db->prepare("
            SELECT cm.*, u.first_name, u.profile_photo 
            FROM chat_messages cm
            JOIN users u ON cm.sender_id = u.id
            WHERE cm.id = ?
        ");
        $stmt->execute([$data->message_id]);
        $message = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($message) {
            $this->broadcastToConversation(
                $data->conversation_id,
                json_encode([
                    'type' => 'message',
                    'message' => $message
                ]),
                $from
            );
        }
    }

    protected function handleCallSignaling($data) {
        if (isset($this->userConnections[$data->receiver_id])) {
            $this->userConnections[$data->receiver_id]->send(json_encode($data));
        }
    }

    protected function updateOnlineStatus($userId, $isOnline) {
        $stmt = $this->db->prepare("
            INSERT INTO user_online_status (user_id, is_online, last_active_at)
            VALUES (?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE
            is_online = ?, last_active_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $isOnline, $isOnline]);

        // Broadcast status change to relevant users
        $stmt = $this->db->prepare("
            SELECT DISTINCT cp.conversation_id, p.user_id
            FROM chat_participants cp
            JOIN chat_participants p ON cp.conversation_id = p.conversation_id
            WHERE cp.user_id = ? AND p.user_id != ?
        ");
        $stmt->execute([$userId, $userId]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (isset($this->userConnections[$row['user_id']])) {
                $this->userConnections[$row['user_id']]->send(json_encode([
                    'type' => 'status_update',
                    'user_id' => $userId,
                    'is_online' => $isOnline
                ]));
            }
        }
    }
}

// Create event loop
$loop = Loop::get();

// Create socket server
$socket = new Server('0.0.0.0:8080', $loop);

// Create WebSocket server
$webSocket = new IoServer(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    $socket
);

echo "Chat server started on port 8080\n";

// Run the server
$loop->run();
