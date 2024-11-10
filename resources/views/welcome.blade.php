@extends('layout')

@section('content')
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                <img src="{{ asset('home/assets/img/5-removebg.png') }}" alt="">
                {{-- <h1 class="sitename">Elevate ERP</h1> --}}
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#team">Team</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a class="btn-getstarted" target="_blank" href="{{ url('admin') }}">
                Login
            </a>
            {{-- 
        @auth
            <a class="btn-getstarted" href="{{ route('member') }}">
              Dashboard
            </a>
        @else
            <a class="btn-getstarted" href="{{ route('register') }}">
              Get Started
            </a>
        @endauth --}}

        </div>
    </header>

    <main class="main">

        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">

            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                        <h1>Better Solutions For Your Business</h1>
                        <p>Elevate ERP - Menaikkan Efisiensi, Mengangkat Prestasi</p>
                        <div class="d-flex">
                            <a href="#about" class="btn-get-started">About Me</a>
                            <a href="https://www.youtube.com/watch?v=LXb3EKWsInQ"
                                class="glightbox btn-watch-video d-flex align-items-center"><i
                                    class="bi bi-play-circle"></i><span>Watch Video</span></a>
                        </div>
                    </div>
                    <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="200">
                        <img src="{{ asset('home/assets/img/hero-img.png') }}" class="img-fluid animated" alt="">
                    </div>
                </div>
            </div>

        </section><!-- /Hero Section -->

        <!-- Clients Section -->
        <section id="clients" class="clients section light-background">

            <div class="container" data-aos="zoom-in">

                <div class="swiper init-swiper">
                    <script type="application/json" class="swiper-config">
        {
          "loop": true,
          "speed": 600,
          "autoplay": {
            "delay": 5000
          },
          "slidesPerView": "auto",
          "pagination": {
            "el": ".swiper-pagination",
            "type": "bullets",
            "clickable": true
          },
          "breakpoints": {
            "320": {
              "slidesPerView": 2,
              "spaceBetween": 40
            },
            "480": {
              "slidesPerView": 3,
              "spaceBetween": 60
            },
            "640": {
              "slidesPerView": 4,
              "spaceBetween": 80
            },
            "992": {
              "slidesPerView": 5,
              "spaceBetween": 120
            },
            "1200": {
              "slidesPerView": 6,
              "spaceBetween": 120
            }
          }
        }
      </script>
                    <div class="swiper-wrapper align-items-center">
                        @foreach ($clients as $client)
                            <div class="swiper-slide"><img src="{{ asset('storage/' . $client->client_logo) }}"
                                    class="img-fluid" alt=""></div>
                        @endforeach
                    </div>
                </div>

            </div>

        </section><!-- /Clients Section -->

        <!-- About Section -->
        <section id="about" class="about section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>About Us</h2>
            </div><!-- End Section Title -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
                        <p>
                            Elevate ERP adalah sebuah sistem perangkat lunak Enterprise Resource Planning (ERP) yang
                            dirancang untuk membantu perusahaan mengelola berbagai aspek bisnis mereka secara
                            terintegrasi. ERP adalah sistem yang menyatukan berbagai fungsi bisnis, seperti akuntansi,
                            manajemen persediaan, penjualan, sumber daya manusia, dan lainnya, dalam satu platform
                            terpadu.
                        </p>
                        <ul>
                            <li><i class="bi bi-check2-circle"></i> <span>Integrasi dan Visibilitas Data.</span></li>
                            <li><i class="bi bi-check2-circle"></i> <span>Peningkatan Efisiensi Operasional.</span>
                            </li>
                            <li><i class="bi bi-check2-circle"></i> <span>Pengelolaan Sumber Daya yang Optimal</span>
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <p>Keunggulan-keunggulan ini membuat ERP menjadi alat yang sangat berharga bagi perusahaan yang
                            ingin mengoptimalkan operasional dan meningkatkan daya saing di pasar.</p>
                        <a href="#" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
                    </div>

                </div>

            </div>

        </section><!-- /About Section -->

        <!-- Why Us Section -->
        <section id="why-us" class="section why-us light-background" data-builder="section">

            <div class="container-fluid">

                <div class="row gy-4">

                    <div class="col-lg-7 d-flex flex-column justify-content-center order-2 order-lg-1">

                        <div class="content px-xl-5" data-aos="fade-up" data-aos-delay="100">
                            <h3><span>Elevete</span><strong>ERP</strong></h3>
                            <p>
                                Elevate ERP - Menaikkan Efisiensi, Mengangkat Prestasi
                            </p>
                        </div>

                        <div class="faq-container px-xl-5" data-aos="fade-up" data-aos-delay="200">

                            <div class="faq-item faq-active">

                                <h3><span>01</span> Integrasi dan Visibilitas Data:</h3>
                                <div class="faq-content">
                                    <p>Mengintegrasikan data dari berbagai departemen dalam satu sistem, memungkinkan
                                        akses dan analisis real-time untuk pengambilan keputusan yang lebih baik.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div><!-- End Faq item-->

                            <div class="faq-item">
                                <h3><span>02</span> Peningkatan Efisiensi Operasional:
                                </h3>
                                <div class="faq-content">
                                    <p>Otomatisasi proses bisnis mengurangi entri data manual, meningkatkan akurasi, dan
                                        mempercepat operasi.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div><!-- End Faq item-->

                            <div class="faq-item">
                                <h3><span>03</span> Pengelolaan Sumber Daya yang Optimal:</h3>
                                <div class="faq-content">
                                    <p>
                                        Memfasilitasi perencanaan dan alokasi sumber daya, mengelola persediaan, dan
                                        memastikan kepatuhan dengan regulasi, yang secara keseluruhan mengurangi biaya
                                        operasional.
                                    </p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div><!-- End Faq item-->

                        </div>

                    </div>

                    <div class="col-lg-5 order-1 order-lg-2 why-us-img">
                        <img src="{{ asset('home/assets/img/why-us.png') }}" class="img-fluid" alt=""
                            data-aos="zoom-in" data-aos-delay="100">
                    </div>
                </div>

            </div>

        </section><!-- /Why Us Section -->

        <!-- Skills Section -->
        <section id="skills" class="skills section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row">

                    <div class="col-lg-6 d-flex align-items-center">
                        <img src="{{ asset('home/assets/img/skills.png') }}" class="img-fluid" alt="">
                    </div>

                    <div class="col-lg-6 pt-4 pt-lg-0 content">

                        <h3>ElevateERP</h3>
                        <p class="fst-italic">
                            Aplikasi Enterprice Resource Planning. Menggunakan teknologi sebagai berikut ini :
                        </p>

                        <div class="skills-content skills-animation">

                            <div class="progress">
                                <span class="skill"><span>PHP</span> <i class="val">100%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div><!-- End Skills Item -->

                            <div class="progress">
                                <span class="skill"><span>JavaScript</span> <i class="val">90%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div><!-- End Skills Item -->

                            <div class="progress">
                                <span class="skill"><span>css</span> <i class="val">75%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div><!-- End Skills Item -->

                            <div class="progress">
                                <span class="skill"><span>html</span> <i class="val">55%</i></span>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" role="progressbar" aria-valuenow="55" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div><!-- End Skills Item -->

                        </div>

                    </div>
                </div>

            </div>

        </section><!-- /Skills Section -->

        <!-- Services Section -->
        <section id="services" class="services section light-background">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Services</h2>
                <p>Berikut adalah empat fungsi utama dari sistem ERP:</p>
            </div><!-- End Section Title -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-activity icon"></i></div>
                            <h4><a href="" class="stretched-link">Manajemen Keuangan</a></h4>
                            <p>Mengelola semua aspek keuangan, termasuk akuntansi, penganggaran, pelaporan keuangan, dan
                                analisis biaya. Fungsi ini memastikan keakuratan data keuangan dan membantu dalam
                                pelaporan serta kepatuhan dengan standar keuangan.</p>
                        </div>
                    </div><!-- End Service Item -->

                    <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-bounding-box-circles icon"></i></div>
                            <h4><a href="" class="stretched-link">Manajemen Sumber Daya Manusia (SDM)</a></h4>
                            <p>Mengelola data karyawan, penggajian, rekrutmen, pelatihan, dan evaluasi kinerja. Fungsi
                                ini membantu dalam pengelolaan tenaga kerja secara efisien dan memastikan kepatuhan
                                dengan peraturan ketenagakerjaan.
                            </p>
                        </div>
                    </div><!-- End Service Item -->

                    <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-calendar4-week icon"></i></div>
                            <h4><a href="" class="stretched-link">Manajemen Rantai Pasokan dan Produksi</a>
                            </h4>
                            <p>Mengatur aliran barang dan jasa dari pemasok ke pelanggan, termasuk pengadaan, manajemen
                                persediaan, dan distribusi. Fungsi ini juga mencakup perencanaan produksi, penjadwalan,
                                dan pengendalian kualitas.
                            </p>
                        </div>
                    </div><!-- End Service Item -->

                    <div class="col-xl-3 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-broadcast icon"></i></div>
                            <h4><a href="" class="stretched-link">Manajemen Hubungan Pelanggan (CRM)</a></h4>
                            <p>Mengelola interaksi dengan pelanggan, termasuk penjualan, layanan pelanggan, dan
                                pemasaran. Fungsi ini membantu dalam menjaga hubungan yang kuat dengan pelanggan dan
                                meningkatkan kepuasan pelanggan melalui layanan yang lebih baik.</p>
                        </div>
                    </div><!-- End Service Item -->

                </div>

            </div>

        </section><!-- /Services Section -->

        <!-- Call To Action Section -->
        <section id="call-to-action" class="call-to-action section dark-background">

            <img src="{{ asset('home/assets/img/cta-bg.jpg') }}" alt="">

            <div class="container">

                <div class="row" data-aos="zoom-in" data-aos-delay="100">
                    <div class="col-xl-9 text-center text-xl-start">
                        <h3>Call To Action</h3>
                        <p>"Integrasi total untuk efisiensi maksimal! Elevate ERP menyatukan semua proses bisnis Anda
                            dalam satu sistem, meningkatkan produktivitas dan meminimalkan kesalahan. Jadikan bisnis
                            Anda lebih cerdas dan lebih cepat!"</p>
                    </div>
                    <div class="col-xl-3 cta-btn-container text-center">
                        <a class="cta-btn align-middle" target="_blank" href="https://wa.me/081654932383">Register Now</a>
                    </div>
                </div>

            </div>

        </section><!-- /Call To Action Section -->

        <!-- Team Section -->
        <section id="team" class="team section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Team</h2>
                <p>Support the team</p>
            </div><!-- End Section Title -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="team-member d-flex align-items-start">
                            <div class="pic"><img src="{{ asset('home/assets/img/team/team-1.jpg') }}"
                                    class="img-fluid" alt=""></div>
                            <div class="member-info">
                                <h4>Irvan</h4>
                                <span>Networking</span>
                                <p>"Jangan tunggu momen yang sempurna; ambil momen tersebut dan jadikan sempurna."</p>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""> <i class="bi bi-linkedin"></i> </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Team Member -->

                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="team-member d-flex align-items-start">
                            <div class="pic"><img src="{{ asset('home/assets/img/team/team-2.jpg') }}"
                                    class="img-fluid" alt=""></div>
                            <div class="member-info">
                                <h4>Wahyu Dedik</h4>
                                <span>Programmer</span>
                                <p>"Kegagalan adalah kesempatan untuk memulai lagi dengan lebih cerdas."</p>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""> <i class="bi bi-linkedin"></i> </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Team Member -->

                </div>

            </div>

        </section><!-- /Team Section -->

        <!-- Contact Section -->
        <section id="contact" class="contact section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Contact</h2>
                <p>Hubungi kami sekarang disini: </p>
            </div><!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">

                    <div class="col-lg-5">

                        <div class="info-wrap">
                            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                                <i class="bi bi-geo-alt flex-shrink-0"></i>
                                <div>
                                    <h3>Address</h3>
                                    {{-- {!! $abouts->address !!} --}}
                                </div>
                            </div><!-- End Info Item -->

                            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                                <i class="bi bi-telephone flex-shrink-0"></i>
                                <div>
                                    <h3>Call Us</h3>
                                    {{-- <p>{{ $abouts->tlp }}</p> --}}
                                </div>
                            </div><!-- End Info Item -->

                            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                                <i class="bi bi-envelope flex-shrink-0"></i>
                                <div>
                                    <h3>Email Us</h3>
                                    {{-- {!! $abouts->email !!} --}}
                                </div>
                            </div><!-- End Info Item -->

                        </div>
                    </div>

                    <div class="col-lg-7">

                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d48389.78314118045!2d-74.006138!3d40.710059!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1676961268712!5m2!1sen!2sus"
                            frameborder="0" style="border:0; width: 100%; height: 370px;" allowfullscreen=""
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                    </div><!-- End Contact Form -->

                </div>

            </div>

        </section><!-- /Contact Section -->

    </main>

    <footer id="footer" class="footer">

        <div class="footer-newsletter">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-6">
                        <h4>Join Our Newsletter</h4>
                        <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
                        <form action="forms/newsletter.php" method="post" class="php-email-form">
                            <div class="newsletter-form"><input type="email" name="email"><input type="submit"
                                    value="Subscribe"></div>
                            <div class="loading">Loading</div>
                            <div class="error-message"></div>
                            <div class="sent-message">Your subscription request has been sent. Thank you!</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.html" class="d-flex align-items-center">
                        <span class="sitename">Elevate ERP</span>
                    </a>
                    <div class="footer-contact pt-3">
                        {{-- {!! $abouts->address !!} --}}
                        {{-- <p class="mt-3"><strong>Phone:</strong> <span>{{ $abouts->tlp }}</span></p> --}}
                        {{-- <p><strong>Email:</strong> <span>{{ $abouts->email }}</span></p> --}}
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Useful Links</h4>
                    <ul>
                        <li><i class="bi bi-chevron-right"></i> <a href="#">Dashboard</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="#">Disclaimer</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="#">Privacy Policy</a></li>
                        <li><i class="bi bi-chevron-right"></i> <a href="#">Terms of service</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Our Services</h4>
                    <ul>
                        <li><i class="bi bi-chevron-right"></i> <a href="#">ERP</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-12">
                    <h4>Follow Us</h4>
                    <p>Elevate ERP - Menaikkan Efisiensi, Mengangkat Prestasi</p>
                    <div class="social-links d-flex">
                        <a href=""><i class="bi bi-twitter-x"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">ElevateERP</strong> <span>All Rights
                    Reserved</span>
            </p>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>
@endsection
