<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Failed</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet"/>
  <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #ffffff;
      font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 20px;
    }

    .card {
      background: #f44336;
      color: #fff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 480px;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    }

    h1 {
      font-size: 38px;
      font-weight: 900;
      margin-bottom: 10px;
    }

    p {
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .countdown {
      font-size: 18px;
      font-weight: bold;
      color: #ffe082;
    }

    .animation-container {
      margin-bottom: 20px;
    }

    dotlottie-player {
      width: 100%;
      max-width: 320px;
      height: auto;
      margin: 0 auto;
    }

    @media (max-width: 600px) {
      .card {
        padding: 25px 20px;
        max-width: 95%;
      }

      h1 {
        font-size: 28px;
      }

      p {
        font-size: 14px;
      }

      dotlottie-player {
        max-width: 260px;
      }
    }

    @media (max-width: 400px) {
      h1 {
        font-size: 24px;
      }

      p {
        font-size: 13px;
      }
    }
  </style>
  <script>
    let countdown = 3;
    function updateCountdown() {
      if (countdown > 0) {
        document.getElementById("countdown").innerText = countdown;
        countdown--;
        setTimeout(updateCountdown, 1000);
      } else {
        window.location.href = "https://mahajong.club/";
      }
    }

    window.onload = updateCountdown;
  </script>
</head>
<body>
  <div class="card">
    <div class="animation-container">
      <dotlottie-player 
        src="https://lottie.host/3023e536-b4b4-4f8b-9124-c1e78fd2591d/EB69xil1LN.lottie" 
        background="transparent" 
        speed="1" 
        loop 
        autoplay>
      </dotlottie-player>
    </div>
    <h1>Payment Failed</h1>
    <p>Oops! Something went wrong with your transaction.<br>Please try again later.</p>
    <p class="countdown">Redirecting to home in <span id="countdown">3</span> seconds...</p>
  </div>
</body>
</html>
