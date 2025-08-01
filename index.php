<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaffSync - Coming Soon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #363636 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            overflow: hidden;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .logo {
            width: 200px;
            height: auto;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }
        .title {
            font-size: 3.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #00ffcc, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            animation: fadeIn 1s ease-out forwards;
        }
        .subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #cccccc;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }
        .countdown {
            font-size: 2rem;
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeIn 1s ease-out 1s forwards;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div id="particles-js" class="particles"></div>
    <div class="container">
        <img src="assets/img/logo.png" alt="StaffSync Logo" class="logo">
        <h1 class="title">StaffSync</h1>
        <p class="subtitle">Something amazing is coming soon</p>
        <div class="countdown" id="countdown"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Initialize particles.js
        particlesJS("particles-js", {
            particles: {
                number: { value: 80 },
                color: { value: "#ffffff" },
                shape: { type: "circle" },
                opacity: { value: 0.5 },
                size: { value: 3 },
                move: { speed: 2 }
            }
        });

        // Countdown timer
        const launchDate = new Date('August 31, 2025 23:59:59').getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = launchDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('countdown').innerHTML = 
                `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>