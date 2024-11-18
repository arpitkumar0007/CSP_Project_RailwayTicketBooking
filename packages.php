<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "railway_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle booking POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), associative: true);
    $userEmail = $conn->real_escape_string($data['userEmail']);
    $packageName = $conn->real_escape_string($data['packageName']);
    $packageDuration = $conn->real_escape_string($data['packageDuration']);
    $packagePrice = $conn->real_escape_string($data['packagePrice']);

    // Check if package is already booked
    $checkQuery = "SELECT id FROM package_bookings WHERE user_email = '$userEmail' AND package_name = '$packageName'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Package already booked']);
        exit;
    }

    // Insert booking
    $sql = "INSERT INTO package_bookings (user_email, package_name, package_duration, package_price) 
            VALUES ('$userEmail', '$packageName', '$packageDuration', '$packagePrice')";

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Package booked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Booking failed']);
    }
    exit;
}

// Handle GET request for checking booking status
if (isset($_GET['check_booking']) && isset($_GET['email'])) {
    $userEmail = $conn->real_escape_string($_GET['email']);
    $sql = "SELECT package_name FROM package_bookings WHERE user_email = '$userEmail'";
    $result = $conn->query($sql);

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row['package_name'];
    }

    echo json_encode($bookings);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.jpg" />
    <title>Travel Packages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="./css/packages.css">
    <link rel="stylesheet" href="./css/common.css">
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>

    <style>
        .book-btn.booked {
            background-color: #808080;
            cursor: not-allowed;
        }

        .book-btn.loading {
            opacity: 0.7;
            cursor: wait;
        }
    </style>
</head>

<body>
    <nav>
        <div class="logo" data-aos="fade-down" data-aos-duration="1000">
        <a href="./index.html">RailYatri</a>
        </div>
        <div class="links">
            <a href="./index.html">Home</a>
            <a href="booktickets.php">Book Tickets</a>
            <a href="findtrains.php">Find Trains</a>
            <a href="pnrstatus.php">PNR Status</a>
            <a href="feedback.php">Feedback</a>
            <a href="packages.html">Travel Packages</a>
            <a href="contactus.php">Contact Us</a>
        </div>
        <div class="buttons" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">
            <!-- Auth buttons -->
            <div id="authButtons">
                <button id="loginBtn" onclick="window.location.href='./loginpage.html'">Register</button>
            </div>
            <!-- User profile -->
            <div class="user-profile" id="userProfile">
                <img id="userPhoto" src="" alt="Profile">
                <span id="userName"></span>
                <div class="dropdown-menu">
                    <button onclick="signOut()">Sign Out</button>
                </div>
            </div>
        </div>
    </nav>


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
                        <img src="./triangle.webp" alt="Golden Triangle Tour" />
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
                                <img src="./qutub.webp" alt="Delhi" />
                                <div class="destination-name">Delhi</div>
                                <div>Red Fort & Qutub Minar</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./tajmahal.webp" alt="Agra" />
                                <div class="destination-name">Agra</div>
                                <div>Taj Mahal & Agra Fort</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./hawamahal.avif" alt="Jaipur" />
                                <div class="destination-name">Jaipur</div>
                                <div>Amber Fort & Hawa Mahal</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹24,999 <small>per person</small>
                            </div>
                            <button class="book-btn"
                                onclick="bookPackage(this, 'Golden Triangle Explorer', '6 Days / 5 Nights', 24999)">Book
                                Now</button>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Package 2 -->
            <label class="card" for="item-2">
                <div class="package-card">
                    <div class="package-header">
                        <img src="./kerala.webp" alt="Kerala Tour" />
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
                                <img src="./kochi.jpg" alt="Kochi" />
                                <div class="destination-name">Kochi</div>
                                <div>Chinese Fishing Nets</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./allepy.jpeg" alt="Alleppey" />
                                <div class="destination-name">Alleppey</div>
                                <div>Backwater Cruise</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./beachresort.jpg" alt="Kovalam" />
                                <div class="destination-name">Kovalam</div>
                                <div>Beach Resort</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹19,999 <small>per person</small>
                            </div>
                            <button class="book-btn"
                                onclick="bookPackage(this, 'Kerala Backwaters Paradise', '5 Days / 4 Nights', 19999)">Book
                                Now</button>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Package 3 -->
            <label class="card" for="item-3">
                <div class="package-card">
                    <div class="package-header">
                        <img src="./himalaya.webp" alt="Himalayan Tour" />
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
                                <img src="./hidimba.jpeg" alt="Manali" />
                                <div class="destination-name">Manali</div>
                                <div>Hadimba Temple</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./solang.jpg" alt="Solang Valley" />
                                <div class="destination-name">Solang Valley</div>
                                <div>Adventure Sports</div>
                            </div>
                            <div class="arrow"></div>
                            <div class="destination">
                                <img src="./rohtangpass.jpg" alt="Rohtang Pass" />
                                <div class="destination-name">Rohtang Pass</div>
                                <div>Snow Paradise</div>
                            </div>
                        </div>
                        <div class="package-footer">
                            <div class="price">
                                ₹29,999 <small>per person</small>
                            </div>
                            <button class="book-btn"
                                onclick="bookPackage(this, 'Himalayan Adventure', '7 Days / 6 Nights', 29999)">Book
                                Now</button>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>

    <script>

        const firebaseConfig = {
            apiKey: "AIzaSyAQASjqmTbXxTZ7Xd-ritsGMwgXI-FqPeI",
            authDomain: "railwayproject-3b59e.firebaseapp.com",
            projectId: "railwayproject-3b59e",
            storageBucket: "railwayproject-3b59e.appspot.com",
            messagingSenderId: "822588329946",
            appId: "1:822588329946:web:96b45081a7dd1418df7ae3"
        };

        firebase.initializeApp(firebaseConfig);

        function getCurrentUserEmail() {
            const auth = firebase.auth();
            return auth.currentUser ? auth.currentUser.email : null;
        }

        async function bookPackage(button, packageName, packageDuration, packagePrice) {
            const userEmail = getCurrentUserEmail();

            if (!userEmail) {
                alert('Please login to book a package');
                window.location.href = 'loginpage.html';
                return;
            }

            // Add loading state
            button.classList.add('loading');
            button.disabled = true;
            button.textContent = 'Booking...';

            try {
                const response = await fetch('packages.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        userEmail,
                        packageName,
                        packageDuration,
                        packagePrice
                    })
                });

                const data = await response.json();

                if (data.success) {
                    button.textContent = 'Booked';
                    button.disabled = true;
                    button.classList.add('booked');
                    alert('Package booked successfully!');
                } else {
                    button.textContent = 'Book Now';
                    button.disabled = false;
                    alert(data.message);
                }
            } catch (error) {
                button.textContent = 'Book Now';
                button.disabled = false;
                alert('Error booking package: ' + error.message);
            } finally {
                button.classList.remove('loading');
            }
        }

        async function checkBookingStatus() {
            const userEmail = getCurrentUserEmail();
            if (!userEmail) return;

            try {
                const response = await fetch(`packages.php?check_booking=1&email=${userEmail}`);
                const bookings = await response.json();

                document.querySelectorAll('.book-btn').forEach(button => {
                    const packageName = button.closest('.package-card').querySelector('.package-title').textContent;
                    if (bookings.includes(packageName)) {
                        button.textContent = 'Booked';
                        button.disabled = true;
                        button.classList.add('booked');
                    }
                });
            } catch (error) {
                console.error('Error checking booking status:', error);
            }
        }
        const authButtons = document.getElementById('authButtons');
        const userProfile = document.getElementById('userProfile');
        const userPhoto = document.getElementById('userPhoto');
        const userName = document.getElementById('userName');

        if (!authButtons) console.error('authButtons element not found');
        if (!userProfile) console.error('userProfile element not found');
        if (!userPhoto) console.error('userPhoto element not found');
        if (!userName) console.error('userName element not found');

        // Listen for auth state changes
        console.log('Setting up auth state listener...');
        firebase.auth().onAuthStateChanged((user) => {
            console.log('Auth state changed:', user ? 'User signed in' : 'User signed out');

            if (user) {
                // User is signed in
                console.log('User details:', {
                    email: user.email,
                    displayName: user.displayName,
                    photoURL: user.photoURL,
                    uid: user.uid
                });

                try {
                    authButtons.style.display = 'none';
                    userProfile.style.display = 'flex';

                    // Set user info
                    if (user.photoURL) {
                        userPhoto.src = user.photoURL;
                        console.log('Set user photo:', user.photoURL);
                    } else {
                        userPhoto.src = 'https://via.placeholder.com/40';
                        console.log('Set default photo');
                    }
                    userName.textContent = user.displayName || user.email;
                    console.log('Set user name:', userName.textContent);
                } catch (error) {
                    console.error('Error updating UI for signed-in user:', error);
                }
            } else {
                // User is signed out
                try {
                    authButtons.style.display = 'flex';
                    userProfile.style.display = 'none';
                    console.log('UI updated for signed-out state');
                } catch (error) {
                    console.error('Error updating UI for signed-out user:', error);
                }
            }
        });

        // Toggle dropdown menu with error handling
        if (userProfile) {
            userProfile.addEventListener('click', (event) => {
                console.log('Profile clicked, toggling dropdown');
                try {
                    userProfile.classList.toggle('active');
                } catch (error) {
                    console.error('Error toggling dropdown:', error);
                }
            });
        }

        // Authentication functions
        async function login() {
            console.log('Starting login process...');
            try {
                // Create Google auth provider
                const provider = new firebase.auth.GoogleAuthProvider();
                console.log('Google auth provider created');

                provider.addScope('profile');
                provider.addScope('email');

                const result = await firebase.auth().signInWithPopup(provider);
                console.log('Login successful:', result.user.email);
                return result;
            } catch (error) {
                console.error('Login error details:', {
                    code: error.code,
                    message: error.message,
                    email: error.email,
                    credential: error.credential
                });
                alert(`Login failed: ${error.message}`);
            }
        }

        async function signup() {
            console.log('Starting signup process...');
            return login(); // Currently using Google sign-in
        }

        async function signOut() {
            console.log('Starting sign out process...');
            try {
                await firebase.auth().signOut();
                console.log('Sign out successful');
            } catch (error) {
                console.error('Sign out error:', error);
                alert(`Sign out failed: ${error.message}`);
            }
        }

        // Add this after your Firebase initialization
        function checkAuthForBooking(event, element) {
            event.preventDefault();

            firebase.auth().onAuthStateChanged((user) => {
                if (user) {
                    // User is signed in, submit the form
                    element.closest('form').submit();
                } else {
                    // User is not signed in, show alert and redirect to login
                    alert('Please login to book tickets');
                    window.location.href = 'loginpage.html';
                }
            });
        }

        // Check booking status when page loads and when auth state changes
        document.addEventListener('DOMContentLoaded', checkBookingStatus);
        firebase.auth().onAuthStateChanged(checkBookingStatus);
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        AOS.init();
    </script>
</body>
<footer class="footer">
    <div class="footer-content">
        <!-- Main Footer -->
        <div class="footer-main">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./logo.jpg" alt="RailYatri" class="footer-logo-img">
                    <p>Book your journey with comfort and ease</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="booktickets.php">Book Tickets</a></li>
                    <li><a href="findtrains.php">Find Trains</a></li>
                    <li><a href="pnrstatus.php">PNR Status</a></li>
                    <li><a href="packages.html">Travel Packages</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Help & Support</h3>
                <ul>
                    <li><a href="contactus.php">Contact Us</a></li>
                    <li><a href="feedback.php">Feedback</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li>Email: railyatri203@gmail.com</li>
                    <li>Phone: +91 7999399604</li>
                    <li>Address: Kanhar Hotsel, IIT Bhilai Kutelabhata Chhatisgarh</li>
                </ul>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="copyright">
                <p>&copy; 2024 RailYatri. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</html>