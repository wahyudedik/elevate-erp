<!DOCTYPE html>
<html>

<head>
    <title>{{ $recruitment->job_title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .poster {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .company-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .company-logo img {
            height: 80px;
        }

        h1 {
            color: #1a202c;
            font-size: 42px;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .meta-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .meta-item {
            background: #f7fafc;
            padding: 10px 20px;
            border-radius: 50px;
            color: #4a5568;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description {
            color: #4a5568;
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .footer {
            text-align: center;
            padding-top: 30px;
            border-top: 2px solid #edf2f7;
        }

        .apply-button {
            background: #4c51bf;
            color: white;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
        }

        .qr-code {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="poster">
        <div class="company-logo">
            <img src="{{ asset('storage/' . $recruitment->company->logo) }}" alt="Company Logo">
        </div>

        <h1>{{ $recruitment->job_title }}</h1>

        <div class="meta-info">
            <div class="meta-item">
                📍 {{ $recruitment->location }}
            </div>
            <div class="meta-item">
                💼 {{ ucfirst(str_replace('_', ' ', $recruitment->employment_type)) }}
            </div>
            <div class="meta-item">
                📅 Posted: {{ $recruitment->posted_date->format('d M Y') }}
            </div>
        </div>

        <div class="description">
            {!! $recruitment->job_description !!}
        </div>

        <div class="footer">
            <div>Apply before: {{ $recruitment->closing_date?->format('d M Y') ?? 'Open' }}</div>
            <a href="{{ route('candidate.apply', $recruitment) }}" class="apply-button">Apply Now</a>

            <div class="qr-code">
                {!! QrCode::size(150)->generate(route('candidate.apply', $recruitment)) !!}
            </div>
        </div>
    </div>
</body>

</html>
