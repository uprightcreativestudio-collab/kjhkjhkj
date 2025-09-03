
document.addEventListener('DOMContentLoaded', () => {
    // 1. Service Worker Registration
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js')
            .then(registration => {
                console.log('Service Worker registered with scope:', registration.scope);
                // Initialize push notifications
                initializePushNotifications(registration);
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    }

    // 2. PWA Install Prompt Handling
    let deferredPrompt;
    const installContainer = document.getElementById('install-container');
    const installButton = document.getElementById('install-button');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        // Update UI to notify the user they can install the PWA
        if (installContainer) {
            installContainer.style.display = 'block';
        }
    });

    if (installButton) {
        installButton.addEventListener('click', async () => {
            // Hide the app provided install promotion
            if (installContainer) {
                installContainer.style.display = 'none';
            }
            // Show the install prompt
            if (deferredPrompt) {
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                // We've used the prompt, and can't use it again, throw it away
                deferredPrompt = null;
            }
        });
    }

    window.addEventListener('appinstalled', () => {
        // Hide the install promotion
        if (installContainer) {
            installContainer.style.display = 'none';
        }
        deferredPrompt = null;
        console.log('PWA was installed');
    });
});

// Function to ask for push notification permission
async function initializePushNotifications(registration) {
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications.');
        return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        console.log('Push notification permission not granted.');
        return;
    }
    console.log('Push notification permission granted.');
    
    // Get push subscription
    try {
        // Get VAPID public key from server
        const vapidResponse = await fetch('../get_vapid_key.php');
        const vapidData = await vapidResponse.json();
        
        if (!vapidData.success) {
            throw new Error('Failed to get VAPID key');
        }
        
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidData.publicKey)
        });
        
        // Save subscription to server
        await saveSubscription(subscription, 'guru');
    } catch (error) {
        console.error('Failed to subscribe to push notifications:', error);
    }
}

// Save subscription to server
async function saveSubscription(subscription, userType) {
    try {
        const response = await fetch('../save_push_subscription.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                subscription: subscription,
                user_type: userType
            })
        });
        
        if (response.ok) {
            console.log('Push subscription saved successfully');
        }
    } catch (error) {
        console.error('Failed to save push subscription:', error);
    }
}

// Helper function to convert VAPID key
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
