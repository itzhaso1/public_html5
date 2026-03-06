
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title -->
    <title>{{ $settings?->name }} | @yield('pageTitle')</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Swiper -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Favicon (هنا المكان الصحيح والرئيسي) -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo/aaa.png') }}">


    <!-- Meta Description -->
    <meta name="description" content="اشحن جواهر فري فاير أو شدات ببجي بسرعة وأمان. متجر سعودي موثوق يوفر توصيل فوري ودعم عربي 24/7.">

    <!-- Keywords -->
    <meta name="keywords" content="شحن فري فاير, جواهر فري فاير, شدات ببجي, متجر الممالك, متجر كينق جيم, Free Fire top up, PUBG UC, شحن ألعاب السعودية, متجر ألعاب موثوق, شراء جواهر فري فاير, شراء شدات ببجي, فري فاير السعودية, ببجي السعودية, متجر شحن فوري">

    <!-- Open Graph -->
    <meta property="og:title" content="متجر الممالك | شحن جواهر فري فاير و شدات ببجي">
    <meta property="og:description" content="أفضل متجر سعودي لشحن جواهر فري فاير وشدات ببجي بسرعة وأمان. توصيل فوري ودعم عربي.">
    <meta property="og:image" content="https://king2game.com/public/assets/images/logo/aaa.jpeg">
    <meta property="og:url" content="https://king2game.com">
    <meta property="og:type" content="website">
    @stack('css')
</head>


    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f1f1f1;
            color: #1F2937;
        }

        .swiper-container {
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 0.5rem;
            display: block;
        }

        .product-img {
            width: 100%;
            aspect-ratio: 2 / 1;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        nav {
            background-color: #000;
            color: #fff;
            padding: 0.5rem 1rem;
            border-bottom: 1.5px solid #ff0000;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .btn-primary {
            background-color: #000;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            width: 100%;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #FBBF24;
            color: #000;
        }

        footer {
            background-color: #000;
            color: #fff;
            padding: 1.5rem;
            width: 100%;
        }

        footer .payment-icons img {
            height: 30px;
            margin-right: 0.5rem;
        }

        .marquee {
            display: inline-flex;
            gap: 2rem;
            width: max-content;
            animation: marquee 20s linear infinite;
            direction: rtl;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .currency-btn {
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .currency-btn:hover {
            transform: scale(1.2);
        }

        .select-all {
            cursor: pointer;
            user-select: all;
        }

        .select-all:hover {
            text-decoration: underline;
        }

        /* الشارات */
        .badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
        }

        .badge-sold {
            background-color: #dc2626;
            color: #fff;
        }

        .badge-sale {
            background-color: #facc15;
            color: #000;
        }
    </style>


    <body>
