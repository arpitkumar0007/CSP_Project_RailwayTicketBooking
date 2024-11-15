<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Packages - RailwayYatri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            padding: 2rem;
        }

        .links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .link a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .link a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* New Carousel/Slider Styles */
        .packages-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            height: 800px;
            perspective: 1000px;
        }

        input[type="radio"] {
            display: none;
        }

        .cards {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
        }

        .card {
            position: absolute;
            width: 60%;
            height: 100%;
            left: 0;
            right: 0;
            margin: auto;
            transition: transform 0.5s ease, opacity 0.5s ease;
            cursor: pointer;
            opacity: 0;
            transform: scale(0.8) translateX(-100%);
        }

        #item-1:checked ~ .cards .card[for="item-1"],
        #item-2:checked ~ .cards .card[for="item-2"],
        #item-3:checked ~ .cards .card[for="item-3"] {
            opacity: 1;
            transform: scale(1) translateX(0);
            z-index: 1;
        }

        #item-1:checked ~ .cards .card[for="item-2"],
        #item-2:checked ~ .cards .card[for="item-3"],
        #item-3:checked ~ .cards .card[for="item-1"] {
            opacity: 0.7;
            transform: scale(0.8) translateX(100%);
        }

        #item-1:checked ~ .cards .card[for="item-3"],
        #item-2:checked ~ .cards .card[for="item-1"],
        #item-3:checked ~ .cards .card[for="item-2"] {
            opacity: 0.7;
            transform: scale(0.8) translateX(-100%);
        }

        /* Original Card Styles */
        .package-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            height: 100%;
        }

        .package-header {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .package-header img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .package-header:hover img {
            transform: scale(1.1);
        }

        .package-content {
            padding: 2rem;
        }

        .package-title {
            font-size: 1.8rem;
            color: #1e3c72;
            margin-bottom: 1rem;
        }

        .package-duration {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .highlights {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .highlight {
            background: #e3f2fd;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #1e3c72;
        }

        .journey-path {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem 0;
            position: relative;
            gap: 1.5rem;
            padding: 1rem;
        }

        .destination {
            flex: 0 1 auto;
            width: 150px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .destination img {
            width: 100%;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 1rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .destination-name {
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 0.5rem;
        }

        .arrow {
            flex: 0 0 50px;
            height: 2px;
            background: repeating-linear-gradient(90deg, #1e3c72, #1e3c72 6px, transparent 6px, transparent 12px);
            position: relative;
            margin-top: -50px;
        }

        .package-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 2rem;
            border-top: 1px solid #eee;
        }

        .price {
            font-size: 1.8rem;
            color: #1e3c72;
            font-weight: bold;
        }

        .price small {
            font-size: 1rem;
            color: #666;
        }

        .book-btn {
            background: #1e3c72;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .book-btn:hover {
            background: #2a5298;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .packages-container {
                height: auto;
                perspective: none;
            }

            .card {
                position: relative;
                width: 100%;
                margin-bottom: 2rem;
                opacity: 1;
                transform: none !important;
            }

            .cards {
                display: flex;
                flex-direction: column;
                gap: 2rem;
                transform-style: flat;
            }

            .journey-path {
                flex-direction: column;
                gap: 2rem;
            }
            
            .arrow {
                width: 2px;
                height: 40px;
                transform: rotate(90deg);
                margin: 0;
            }
            
            .destination {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="links">
        <div class="link" data-aos="fade-up"><a href="">Home</a></div>
        <div class="link" data-aos="fade-up" data-aos-delay="200"><a href="">Find Trains</a></div>
        <div class="link" data-aos="fade-up" data-aos-delay="300"><a href="">PNR Status</a></div>
        <div class="link" data-aos="fade-up" data-aos-delay="400"><a href="">Travel Packages</a></div>
        <div class="link" data-aos="fade-up" data-aos-delay="500"><a href="">Contact</a></div>
    </div>

    <h1 class="page-title" data-aos="fade-up">Discover India with Our Curated Travel Packages</h1>

    <div class="packages-container">
        <input type="radio" name="slider" id="item-1" checked>
        <input type="radio" name="slider" id="item-2">
        <input type="radio" name="slider" id="item-3">
        
        <div class="cards">
            <!-- Package 1 -->
            <label class="card" for="item-1">
                <div class="package-card">
                    <div class="package-header">
                        <img src="https://source.unsplash.com/random/1200x400/?india,delhi" alt="Golden Triangle Tour" />
                    </div>
                    <div class="package-content">
                        <h2 class="package-title">Golden Triangle Explorer</h2>
                        <p class="package-duration">6 Days / 5 Nights</p>
                        <div class="highlights">
                            <span class="highlight">🏰 UNESCO Sites</span>
                            <span class="highlight">🎨 Cultural Tours</span>
                            <span class="highlight">🍽 Traditional Cuisine</span>
                            <span class="highlight">🚂 Train Journey</span>
                        </div>
                        <div class="journey-path">
                            <div class="destination">
                                <img src="https://source.unsplash.com/random/400x320/?delhi" alt="Delhi" />
                                <div class="destination-name">Delhi</div>
                                <div>Red Fort & Qutub Minar</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="https://source.unsplash.com/random/400x320/?agra" alt="Agra" />
                                <div class="destination-name">Agra</div>
                                <div>Taj Mahal & Agra Fort</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="https://source.unsplash.com/random/400x320/?jaipur" alt="Jaipur" />
                                <div class="destination-name">Jaipur</div>
                                <div>Amber Fort & Hawa Mahal</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹24,999 <small>per person</small>
                            </div>
                            <button class="book-btn">Book Now</button>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Package 2 -->
            <label class="card" for="item-2">
                <div class="package-card">
                    <div class="package-header">
                        <img src="/api/placeholder/1200/400" alt="Kerala Tour" />
                    </div>
                    <div class="package-content">
                        <h2 class="package-title">Kerala Backwaters Paradise</h2>
                        <p class="package-duration">5 Days / 4 Nights</p>
                        <div class="highlights">
                            <span class="highlight">🚤 Houseboat Stay</span>
                            <span class="highlight">🌿 Ayurveda Spa</span>
                            <span class="highlight">🏖 Beach Time</span>
                            <span class="highlight">🌴 Nature Walks</span>
                        </div>
                        <div class="journey-path">
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Kochi" />
                                <div class="destination-name">Kochi</div>
                                <div>Chinese Fishing Nets</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Alleppey" />
                                <div class="destination-name">Alleppey</div>
                                <div>Backwater Cruise</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Kovalam" />
                                <div class="destination-name">Kovalam</div>
                                <div>Beach Resort</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹19,999 <small>per person</small>
                            </div>
                            <button class="book-btn">Book Now</button>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Package 3 -->
            <label class="card" for="item-3">
                <div class="package-card">
                    <div class="package-header">
                        <img src="/api/placeholder/1200/400" alt="Himalayan Tour" />
                    </div>
                    <div class="package-content">
                        <h2 class="package-title">Himalayan Adventure</h2>
                        <p class="package-duration">7 Days / 6 Nights</p>
                        <div class="highlights">
                            <span class="highlight">🏔 Mountain Views</span>
                            <span class="highlight">🛶 River Rafting</span>
                            <span class="highlight">🏯 Monastery Visit</span>
                            <span class="highlight">🏕 Camping</span>
                        </div>
                        <div class="journey-path">
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Manali" />
                                <div class="destination-name">Manali</div>
                                <div>Hadimba Temple</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Solang Valley" />
                                <div class="destination-name">Solang Valley</div>
                                <div>Adventure Sports</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="/api/placeholder/400/320" alt="Rohtang Pass" />
                                <div class="destination-name">Rohtang Pass</div>
                                <div>Snow Paradise</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹29,999 <small>per person</small>
                            </div>
                            <button class="book-btn">Book Now</button>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>