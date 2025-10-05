# Google OAuth Verification Guide

## Error: "Access blocked: meta-shark.onrender.com has not completed the Google verification process"

This error occurs because your Google OAuth application is still in testing mode and hasn't been verified by Google for public use. Until your app is verified, only approved test users can access it.

## Quick Fix: Add Test Users

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project: `practical-yew-473913-a1`
3. Navigate to **APIs & Services** > **OAuth consent screen**
4. In the **Test users** section, click **+ ADD USERS**
5. Add your email address: `LORENZEZZ0987@gmail.com` and any other test users
6. Click **SAVE**

After adding yourself as a test user, try the authentication process again.

## Long-term Solution: Complete Google Verification

If you plan to make your application available to all users, you'll need to complete Google's verification process:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project: `practical-yew-473913-a1`
3. Navigate to **APIs & Services** > **OAuth consent screen**
4. Click **EDIT APP**
5. Complete all required information (Privacy Policy URL, Homepage URL, etc.)
6. Submit your app for verification

The verification process may take several days to complete.

## Handling Unverified App Errors in Your Code

If you want to provide a better user experience while your app is unverified, you can update your code to handle the `access_denied` error:

```php
// In your callback handler (google_callback.php)
if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
    // Redirect to a custom error page or display a helpful message
    echo "<h1>Authentication Error</h1>";
    echo "<p>This application is currently in testing mode. Please contact the administrator to be added as a test user.</p>";
    echo "<p>Your email: " . htmlspecialchars($_SESSION['email'] ?? 'Not available') . " needs to be added to the test users list.</p>";
    echo "<p><a href='login_users.php'>Return to login</a></p>";
    exit;
}
```

## Need Help?

If you continue to experience issues, please contact the Google Cloud Support team or refer to the [OAuth 2.0 documentation](https://developers.google.com/identity/protocols/oauth2).