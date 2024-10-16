<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practice</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-color: grey;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }
        canvas {
            display: block;
        }
        #topImage {
            position: absolute;
            top: 20px; 
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000; 
        }
        .character {
            position: absolute;
            width: 160px;
            height: 190px;
        }
        #stickman2 {
            transform: scaleX(-1);
        }
        #startButton {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px 20px;
            font-size: 20px;
            z-index: 1000;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <img id="topImage" src="escapekey.gif" alt="Top Center Image">
    
    <!-- Character GIFs -->
    <img id="stickman1" class="character" src="RYUGIF1.gif" alt="Stickman 1">
    <img id="stickman2" class="character" src="KENGIF1.gif" alt="Stickman 2">
    
    <canvas id="gameCanvas"></canvas>
    <button id="startButton">Start Game</button>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const stickman1 = document.getElementById('stickman1');
        const stickman2 = document.getElementById('stickman2');

        let stickman1X = canvas.width / 4;
        let stickman1Y = canvas.height - 150;
        let stickman1Health = 100;
        let stickman2Health = 100;
        let stickman1Score = 0;
        let stickman2Score = 0;

        let isJumping1 = false;
        let velocity1Y = 0;
        const gravity = 0.4;
        const jumpPower = -12;

        let stickman2X = (canvas.width * 3) / 4;
        let stickman2Y = canvas.height - 150;
        let isJumping2 = false;
        let velocity2Y = 0;

        const keys = {
            w: false,
            a: false,
            d: false,
            e: false, 
            ArrowUp: false,
            ArrowLeft: false,
            ArrowRight: false,
            '/': false 
        };

        const stabDistance = 100; 
        const damageRadius = 40; 
        let isStabbing1 = false;
        let isStabbing2 = false; 
        let gameRunning = false; // To track if the game is running

        // Function to start the game
        function startGame() {
            document.getElementById('startButton').style.display = 'none'; // Hide the start button
            gameRunning = true; // Set gameRunning to true
            update(); // Start the game loop
        }

        // Event listeners for key presses
        window.addEventListener('keydown', (e) => {
            if (gameRunning) { // Only listen to key events if the game is running
                if (e.key === 'w') keys.w = true;
                if (e.key === 'a') keys.a = true;
                if (e.key === 'd') keys.d = true;
                if (e.key === 'e') {
                    keys.e = true;
                    isStabbing1 = true; 
                }
                if (e.key === 'ArrowUp') {
                    keys.ArrowUp = true;
                    if (!isJumping2) {
                        isJumping2 = true;
                        velocity2Y = jumpPower; 
                    }
                }
                if (e.key === 'ArrowLeft') keys.ArrowLeft = true;
                if (e.key === 'ArrowRight') keys.ArrowRight = true;
                if (e.key === '/') {
                    isStabbing2 = true; 
                }
            }
        });

        window.addEventListener('keyup', (e) => {
            if (e.key === 'w') keys.w = false;
            if (e.key === 'a') keys.a = false;
            if (e.key === 'd') keys.d = false;
            if (e.key === 'e') keys.e = false;
            if (e.key === 'ArrowUp') keys.ArrowUp = false;
            if (e.key === 'ArrowLeft') keys.ArrowLeft = false;
            if (e.key === 'ArrowRight') keys.ArrowRight = false;
            if (keys['/']) keys['/'] = false;
        });

        function drawHealthBars() {
            ctx.fillStyle = "red";
            ctx.fillRect(20, 20, 200, 20);
            ctx.fillStyle = "green";
            ctx.fillRect(20, 20, (stickman1Health / 100) * 200, 20);

            ctx.fillStyle = "red";
            ctx.fillRect(canvas.width - 220, 20, 200, 20);
            ctx.fillStyle = "green";
            ctx.fillRect(canvas.width - 220, 20, (stickman2Health / 100) * 200, 20);
        }

        function drawScores() {
            ctx.fillStyle = "white";
            ctx.font = "20px Arial";
            ctx.fillText(`Player 1 Score: ${stickman1Score}`, 20, 60);
            ctx.fillText(`Player 2 Score: ${stickman2Score}`, canvas.width - 220, 60);
        }

        function updateSwordStab() {
            if (isStabbing1) {
                if (checkSwordCollision(stickman1X + stabDistance, stickman1Y + 20, stickman2X, stickman2Y)) {
                    stickman2Health -= 15; 
                    if (stickman2Health <= 0) {
                        stickman1Score++; 
                        resetGame(); 
                    }
                }
                isStabbing1 = false; 
            }
            if (isStabbing2) {
                if (checkSwordCollision(stickman2X + stabDistance, stickman2Y + 20, stickman1X, stickman1Y)) {
                    stickman1Health -= 15; 
                    if (stickman1Health <= 0) {
                        stickman2Score++; 
                        resetGame(); 
                    }
                }
                isStabbing2 = false; 
            }
        }

        function checkSwordCollision(x1, y1, targetX, targetY) {
            const buffer = damageRadius; 
            return (
                targetX >= x1 - buffer &&
                targetX <= x1 + buffer &&
                targetY >= y1 - buffer &&
                targetY <= y1 + 30 
            );
        }

        function resetGame() {
            stickman1Health = 100;
            stickman2Health = 100;
            stickman1X = canvas.width / 4;
            stickman1Y = canvas.height - 150;
            stickman2X = (canvas.width * 3) / 4;
            stickman2Y = canvas.height - 150;

            stickman1.style.left = `${stickman1X}px`;
            stickman1.style.top = `${stickman1Y}px`;
            stickman2.style.left = `${stickman2X}px`;
            stickman2.style.top = `${stickman2Y}px`;
        }

        function update() {
            if (!gameRunning) return; // Exit if the game is not running
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            drawHealthBars();
            drawScores();

            if (keys.w && !isJumping1) {
                isJumping1 = true;
                velocity1Y = jumpPower;
            }
            if (isJumping1) {
                stickman1Y += velocity1Y;
                velocity1Y += gravity;
                if (stickman1Y >= canvas.height - 150) {
                    stickman1Y = canvas.height - 150;
                    isJumping1 = false;
                }
                stickman1.style.top = `${stickman1Y}px`; 
            }
            if (keys.a) stickman1X -= 5; 
            if (keys.d) stickman1X += 5; 
            stickman1X = Math.max(0, Math.min(stickman1X, canvas.width - 160));
            stickman1.style.left = `${stickman1X}px`; 

            if (keys.ArrowUp && !isJumping2) {
                isJumping2 = true;
                velocity2Y = jumpPower;
            }
            if (isJumping2) {
                stickman2Y += velocity2Y;
                velocity2Y += gravity;
                if (stickman2Y >= canvas.height - 150) {
                    stickman2Y = canvas.height - 150;
                    isJumping2 = false;
                }
                stickman2.style.top = `${stickman2Y}px`;
            }
            if (keys.ArrowLeft) stickman2X -= 5; 
            if (keys.ArrowRight) stickman2X += 5; 
            stickman2X = Math.max(0, Math.min(stickman2X, canvas.width - 160));
            stickman2.style.left = `${stickman2X}px`; 

            updateSwordStab();

            requestAnimationFrame(update);
        }

        // Attach the startGame function to the start button
        document.getElementById('startButton').addEventListener('click', startGame);
    </script>
</body>
</html>
