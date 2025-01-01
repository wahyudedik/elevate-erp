<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Situs Dalam Perbaikan</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            text-align: center;
            max-width: 600px;
        }

        .container h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 1.2rem;
            margin-bottom: 40px;
        }

        .container a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            color: #2575fc;
            background-color: #fff;
            border-radius: 25px;
            text-decoration: none;
            transition: 0.3s;
        }

        .container a:hover {
            background-color: #f1f1f1;
            color: #6a11cb;
        }

        .gear {
            position: relative;
            margin: 20px auto;
            width: 80px;
            height: 80px;
            border: 10px solid #fff;
            border-radius: 50%;
            animation: spin 3s linear infinite;
        }

        .gear::before, .gear::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 40px;
            background-color: #fff;
        }

        .gear::after {
            transform: translate(-50%, -50%) rotate(90deg);
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gear"></div>
        <h1>Situs Sedang Dalam Perbaikan</h1>
        <p>Kami sedang memperbarui sistem untuk memberikan pengalaman terbaik kepada Anda. Silakan kembali lagi nanti!</p>
        <a href="https://wa.me/6281654932383" target="_blank">Hubungi Kami</a>
    </div>
</body>
</html>
