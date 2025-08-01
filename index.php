<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StaffSync Launch Countdown</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      height: 100vh;
      background: #0c0f14;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
    }

    canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 0;
    }

    .countdown-container {
      z-index: 1;
      text-align: center;
      color: #ffffff;
      animation: fadeIn 2s ease-in-out forwards;
    }

    h1 {
      font-size: 4rem;
      background: linear-gradient(90deg, #00ffe0, #00d4ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 2rem;
    }

    .countdown {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
    }

    .time-box {
      background: rgba(0, 255, 200, 0.05);
      border: 1px solid rgba(0, 255, 200, 0.2);
      border-radius: 16px;
      padding: 1.5rem;
      min-width: 120px;
      transition: transform 0.5s;
      backdrop-filter: blur(6px);
      box-shadow: 0 0 20px rgba(0, 255, 200, 0.2);
    }

    .time-box:hover {
      transform: scale(1.1);
    }

    .time-box div {
      font-size: 2.5rem;
      font-weight: bold;
      color: #00ffe0;
    }

    .time-box span {
      font-size: 1rem;
      margin-top: 0.5rem;
      display: block;
      color: #aaa;
    }

    .tagline {
      margin-top: 2rem;
      font-size: 1.1rem;
      color: #ccc;
      font-style: italic;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <canvas id="bgCanvas"></canvas>
  <div class="countdown-container">
    <h1>StaffSync Launches In</h1>
    <div class="countdown">
      <div class="time-box">
        <div id="days">--</div>
        <span>Days</span>
      </div>
      <div class="time-box">
        <div id="hours">--</div>
        <span>Hours</span>
      </div>
      <div class="time-box">
        <div id="minutes">--</div>
        <span>Minutes</span>
      </div>
      <div class="time-box">
        <div id="seconds">--</div>
        <span>Seconds</span>
      </div>
    </div>
    <div class="tagline">Experience the future of HR — powered by AI, crafted for people.</div>
  </div>

  <script>
    const canvas = document.getElementById('bgCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];
    for (let i = 0; i < 150; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 2 + 1,
        speedX: (Math.random() - 0.5) * 0.5,
        speedY: (Math.random() - 0.5) * 0.5
      });
    }

    function animateParticles() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = '#00ffe0';
      particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fill();
        p.x += p.speedX;
        p.y += p.speedY;

        if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
        if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
      });
      requestAnimationFrame(animateParticles);
    }
    animateParticles();

    const launchDate = new Date("August 30, 2025 00:00:00").getTime();
    const updateCountdown = () => {
      const now = new Date().getTime();
      const distance = launchDate - now;

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      document.getElementById("days").innerText = days;
      document.getElementById("hours").innerText = hours;
      document.getElementById("minutes").innerText = minutes;
      document.getElementById("seconds").innerText = seconds;
    };

    setInterval(updateCountdown, 1000);
    updateCountdown();

    window.addEventListener('resize', () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>
</body>
</html>
