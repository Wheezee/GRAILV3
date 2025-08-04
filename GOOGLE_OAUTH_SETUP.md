# Google OAuth Setup Guide

This guide will help you set up Google OAuth authentication for your GRAIL application.

## Prerequisites

1. A Google account
2. Access to Google Cloud Console

## Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API (if not already enabled)

## Step 2: Configure OAuth Consent Screen

1. In Google Cloud Console, go to "APIs & Services" > "OAuth consent screen"
2. Choose "External" user type
3. Fill in the required information:
   - App name: "GRAIL"
   - User support email: Your email
   - Developer contact information: Your email
4. Add scopes:
   - `email`
   - `profile`
   - `openid`
5. Add test users (your email addresses)

## Step 3: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application"
4. Set the following:
   - Name: "GRAIL Web Client"
   - Authorized JavaScript origins: `http://localhost:8000` (for development)
   - Authorized redirect URIs: `http://localhost:8000/auth/google/callback`
5. Click "Create"
6. Note down the Client ID and Client Secret

## Step 4: Configure Environment Variables

Add the following to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

## Step 5: Test the Integration

1. Start your Laravel application: `php artisan serve`
2. Go to `http://localhost:8000/login`
3. Click "Continue with Google"
4. You should be redirected to Google's OAuth consent screen
5. After authorization, you should be redirected back to your dashboard

## Production Deployment

For production, update the following:

1. **Google Cloud Console:**
   - Add your production domain to authorized origins
   - Add your production callback URL to authorized redirect URIs

2. **Environment Variables:**
   ```env
   GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
   ```

## Troubleshooting

### Common Issues:

1. **"redirect_uri_mismatch" error:**
   - Ensure the redirect URI in Google Console matches exactly with your .env file
   - Check for trailing slashes or protocol mismatches

2. **"invalid_client" error:**
   - Verify your Client ID and Client Secret are correct
   - Ensure you're using the right credentials for your environment

3. **"access_denied" error:**
   - Check if your email is added as a test user in OAuth consent screen
   - Verify the app is not in restricted mode

### Security Notes:

- Never commit your `.env` file to version control
- Keep your Client Secret secure
- Use HTTPS in production
- Regularly rotate your OAuth credentials

## Features

The Google OAuth integration provides:

- ✅ One-click login with Google account
- ✅ Automatic user registration for new Google users
- ✅ Seamless integration with existing authentication
- ✅ Support for existing users to link their Google account
- ✅ Secure OAuth 2.0 flow
- ✅ User avatar and profile information from Google

## Support

If you encounter any issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Google Cloud Console error logs
3. Network tab in browser developer tools for redirect issues 