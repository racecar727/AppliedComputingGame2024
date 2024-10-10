<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map 2</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            background-image: url(https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/125ace30-ef27-4e9a-8fb2-210650a3e87c/d9zxso0-6b4f2ef5-4e1d-488b-808f-321bbc0e5415.gif?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7InBhdGgiOiJcL2ZcLzEyNWFjZTMwLWVmMjctNGU5YS04ZmIyLTIxMDY1MGEzZTg3Y1wvZDl6eHNvMC02YjRmMmVmNS00ZTFkLTQ4OGItODA4Zi0zMjFiYmMwZTU0MTUuZ2lmIn1dXSwiYXVkIjpbInVybjpzZXJ2aWNlOmZpbGUuZG93bmxvYWQiXX0.Jh46iHQJP_C_hIq5Ffu9Tm3ZpsymgJL6BZTJovaP90A);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }
        canvas {
            display: block;
        }
    </style>
</head>
<body>
	
	
<canvas id="gameCanvas"></canvas>
<script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const swordImage = new Image();
        swordImage.src = "Fireball1.png"; 
	
        const character1Image = new Image();
        character1Image.src = 'MKC7.png'; 
    
        const character2Image = new Image();
        character2Image.src = 'MKC5edit.png';

        let stickman1X = canvas.width / 4;
        let stickman1Y = canvas.height - 150;
        let stickman1Health = 100;
        let stickman2Health = 100;
        let stickman1Score = 0;
        let stickman2Score = 0;

        let isJumping1 = false;
        let velocity1Y = 0;
        const gravity = 0.5;
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

        window.addEventListener('keydown', (e) => {
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
        });

        window.addEventListener('keyup', (e) => {
            if (e.key === 'w') keys.w = false;
            if (e.key === 'a') keys.a = false;
            if (e.key === 'd') keys.d = false;
            if (e.key === 'e') keys.e = false;
            if (e.key === 'ArrowUp') keys.ArrowUp = false;
            if (e.key === 'ArrowLeft') keys.ArrowLeft = false;
            if (e.key === 'ArrowRight') keys.ArrowRight = false;
            if (e.key === '/') keys['/'] = false;
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

        function drawCharacter1(x, y) {
            ctx.drawImage(character1Image, x - 45, y - 100, 253, 283); 
        }

        function drawCharacter2(x, y) {
            ctx.save();
            ctx.translate(x + 55, 0); 
            ctx.scale(-1, 1); 
            ctx.drawImage(character2Image, -55, y - 100, 253, 283); 
            ctx.restore(); 
        }

        function drawSword(x, y, isStabbing) {
            const angleInDegrees = 270; 
            const angleInRadians = angleInDegrees * (Math.PI / 180); 

            ctx.save();
            ctx.translate(x, y); 
            ctx.rotate(angleInRadians); 

            if (isStabbing) {
                ctx.drawImage(swordImage, stabDistance / -30, -10, 90, 120); 
            } else {
                ctx.drawImage(swordImage, -35, -20, 10000, 15000); 
            }

            ctx.restore(); 
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

        function resetGame() {
            stickman1Health = 100;
            stickman2Health = 100;
            stickman1X = canvas.width / 4;
            stickman1Y = canvas.height - 150;
            stickman2X = (canvas.width * 3) / 4;
            stickman2Y = canvas.height - 150;
        }

        function update() {
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
            }
            if (keys.a) stickman1X -= 5; 
            if (keys.d) stickman1X += 5; 
            stickman1X = Math.max(0, Math.min(stickman1X, canvas.width - 110));

            drawCharacter1(stickman1X, stickman1Y); 

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
            }
            if (keys.ArrowLeft) stickman2X -= 5; 
            if (keys.ArrowRight) stickman2X += 5; 
            stickman2X = Math.max(0, Math.min(stickman2X, canvas.width - 110));

            drawCharacter2(stickman2X, stickman2Y); 

            drawSword(stickman1X, stickman1Y, isStabbing1);
            drawSword(stickman2X, stickman2Y, isStabbing2);

            updateSwordStab();

            requestAnimationFrame(update);
        }

        update();
</script>
</body>
</html>
