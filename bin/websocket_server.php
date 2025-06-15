<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/models/BaseModel.php';
require __DIR__ . '/../app/models/ProfileModel.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;

class ChatServer implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $users;
    protected $profileModel;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        
        // Initialize ProfileModel (it will handle its own database connection)
        try {
            $this->profileModel = new ProfileModel();
            echo "WebSocket server started on port 8080\n";
        } catch (\Exception $e) {
            echo "Error initializing ProfileModel: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            if (!$data || !isset($data['type'])) {
                echo "Invalid message format\n";
                return;
            }
            
            switch($data['type']) {
                case 'connect':
                case 'user_connect':
                    if (!isset($data['user_id'], $data['first_name'])) {
                        echo "Missing user information in connect message\n";
                        return;
                    }
                    
                    try {
                        // Get user's profile photo
                        $profile = $this->profileModel->findByUserId($data['user_id']);
                        
                        $this->users[$from->resourceId] = [
                            'connection' => $from,
                            'userId' => $data['user_id'],
                            'firstName' => $data['first_name'],
                            'lastName' => isset($data['last_name']) ? $data['last_name'] : '',
                            'status' => 'online',
                            'profile_photo' => isset($profile['profile_photo']) ? $profile['profile_photo'] : null
                        ];
                        echo "User {$data['first_name']} " . (isset($data['last_name']) ? $data['last_name'] : '') . " connected (photo: " . ($profile['profile_photo'] ? 'yes' : 'no') . ")\n";
                        $this->broadcastUsersList();
                    } catch (\Exception $e) {
                        echo "Error fetching user profile: " . $e->getMessage() . "\n";
                        // Still connect the user, just without a profile photo
                        $this->users[$from->resourceId] = [
                            'connection' => $from,
                            'userId' => $data['user_id'],
                            'firstName' => $data['first_name'],
                            'lastName' => isset($data['last_name']) ? $data['last_name'] : '',
                            'status' => 'online',
                            'profile_photo' => null
                        ];
                        echo "User {$data['first_name']} " . (isset($data['last_name']) ? $data['last_name'] : '') . " connected (no photo due to error)\n";
                        $this->broadcastUsersList();
                    }
                    break;
                    
                case 'status_change':
                    if (isset($this->users[$from->resourceId])) {
                        $this->users[$from->resourceId]['status'] = $data['status'];
                        echo "User {$this->users[$from->resourceId]['firstName']} changed status to {$data['status']}\n";
                        $this->broadcastUsersList();
                    }
                    break;

                case 'chat_message':
                    if (!isset($data['receiver_id'], $data['sender_id'], $data['message'])) {
                        echo "Missing required fields in chat message\n";
                        return;
                    }
                    $this->broadcastChatMessage($from, $data);
                    break;

                case 'message':
                    if (!isset($data['conversation_id'])) {
                        echo "Missing conversation ID in message\n";
                        return;
                    }
                    $this->broadcastMessage($from, $data);
                    break;
            }
        } catch (\Exception $e) {
            echo "Error processing message: {$e->getMessage()}\n";
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        if (isset($this->users[$conn->resourceId])) {
            $user = $this->users[$conn->resourceId];
            echo "User {$user['firstName']} {$user['lastName']} disconnected\n";
            unset($this->users[$conn->resourceId]);
            $this->broadcastUsersList();
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    protected function broadcastUsersList() {
        $usersList = [];
        foreach ($this->users as $resourceId => $userData) {
            $usersList[] = [
                'id' => $userData['userId'],
                'firstName' => $userData['firstName'],
                'lastName' => $userData['lastName'],
                'status' => $userData['status'],
                'profile_photo' => $userData['profile_photo']
            ];
        }
        
        $message = json_encode([
            'type' => 'users_list',
            'users' => $usersList
        ]);
        
        echo "Broadcasting users list: " . count($usersList) . " users\n";
        
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    protected function broadcastChatMessage($from, $data) {
        $sender = isset($this->users[$from->resourceId]) ? $this->users[$from->resourceId] : null;
        if (!$sender) {
            echo "Sender not found for chat message\n";
            return;
        }

        $message = json_encode([
            'type' => 'new_message',
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'message' => $data['message'],
            'subject' => isset($data['subject']) ? $data['subject'] : 'Chat Message',
            'message_id' => isset($data['message_id']) ? $data['message_id'] : null,
            'created_at' => isset($data['created_at']) ? $data['created_at'] : date('c'),
            'sender' => [
                'id' => $sender['userId'],
                'firstName' => $sender['firstName'],
                'lastName' => $sender['lastName'],
                'profile_photo' => $sender['profile_photo']
            ]
        ]);

        echo "Broadcasting chat message from {$sender['firstName']} to user {$data['receiver_id']}\n";

        // Send to the specific receiver if they're online
        foreach ($this->users as $resourceId => $userData) {
            if ($userData['userId'] == $data['receiver_id']) {
                $userData['connection']->send($message);
                echo "Message sent to receiver\n";
                break;
            }
        }
    }

    protected function broadcastMessage($from, $data) {
        $sender = isset($this->users[$from->resourceId]) ? $this->users[$from->resourceId] : null;
        if (!$sender) return;

        $message = json_encode([
            'type' => 'message',
            'conversation_id' => $data['conversation_id'],
            'message_id' => isset($data['message_id']) ? $data['message_id'] : null,
            'sender' => [
                'id' => $sender['userId'],
                'firstName' => $sender['firstName'],
                'lastName' => $sender['lastName'],
                'profile_photo' => $sender['profile_photo']
            ]
        ]);

        // Send to all connected users in the conversation
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }
}

// Run the server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run(); 