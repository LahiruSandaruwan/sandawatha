<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Sandawatha.lk</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #2d3748;
            margin: 0;
            line-height: 1;
            animation: bounce 2s ease infinite;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .error-title {
            font-size: 32px;
            color: #4a5568;
            margin: 1rem 0;
        }

        .error-message {
            color: #718096;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .home-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: #4299e1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(66, 153, 225, 0.2);
        }

        .home-button:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(66, 153, 225, 0.3);
            color: white;
            text-decoration: none;
        }

        .error-illustration {
            max-width: 300px;
            margin: 2rem auto;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .error-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <canvas class="particles" id="particles"></canvas>
        <div class="error-content">
            <div class="error-container">
                <img src="/assets/images/404-illustration.svg" alt="404 Illustration" class="error-illustration">
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Oops! Page Not Found</h2>
                <p class="error-message">
                    The page you're looking for seems to have wandered off. Don't worry, these things happen to the best of us!
                </p>
                <a href="/" class="home-button">
                    <i class="fas fa-home mr-2"></i>
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        // Particle animation
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 50;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 5 + 1;
                this.speedX = Math.random() * 3 - 1.5;
                this.speedY = Math.random() * 3 - 1.5;
                this.color = `rgba(66, 153, 225, ${Math.random() * 0.5})`;
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.size > 0.2) this.size -= 0.1;

                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }

            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function init() {
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach((particle, index) => {
                particle.update();
                particle.draw();
                
                if (particle.size <= 0.2) {
                    particles.splice(index, 1);
                    particles.push(new Particle());
                }
            });
            
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        init();
        animate();
    </script>
</body>
</html>