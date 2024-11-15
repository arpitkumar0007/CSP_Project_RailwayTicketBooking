// Initialize Firebase with your config
console.log('Starting Firebase initialization...');
const firebaseConfig = {
    apiKey: "AIzaSyAQASjqmTbXxTZ7Xd-ritsGMwgXI-FqPeI",
    authDomain: "railwayproject-3b59e.firebaseapp.com",
    projectId: "railwayproject-3b59e",
    storageBucket: "railwayproject-3b59e.appspot.com",
    messagingSenderId: "822588329946",
    appId: "1:822588329946:web:96b45081a7dd1418df7ae3"
};

try {
    firebase.initializeApp(firebaseConfig);
    console.log('Firebase initialized successfully');
} catch (error) {
    console.error('Firebase initialization error:', error);
}

// Get UI elements with error checking
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

        // Add scopes if needed
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