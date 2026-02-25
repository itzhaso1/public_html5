# Elearning Platform - Usage Guide

<p align="center">
  <img src="public/dashboard/assets/media/logos/logo-default.svg" alt="Elearning-Platform" width="200"/>
</p>

## Login URLs

Below are the login URLs for different user roles:

- **Admin Dashboard:**
  [Login as Admin](http://127.0.0.1:8000/admin/login)

- **Teacher Portal:**
  [Login as Teacher](http://127.0.0.1:8000/teacher/login)

- **Academic Portal:**
  [Login as Academic](http://127.0.0.1:8000/academic/login)

## Repository Pattern Usage ::

To use the repository pattern in this project, follow these steps:

1. **Create an Interface:**

   Use the following command to generate a new interface:

   ```bash
   php artisan make:interface (interfaceName)

2. **Create an Repository:**

   Use the following command to generate a new repository:

   ```bash
   php artisan make:repository (repositoryName)


    Replace (repositoryName) with the name of your repository. For example, AdminRepository.

    Verify the Created Files:
        Ensure that the interface you created is located in the App\Repositories\Contracts directory. For example, AdminRepositoryInterface.
        Ensure that the repository implementation you created is located in the App\Repositories\Eloquents directory. For example, AdminRepository.

Make sure to define the methods in your interface and implement them in your repository to follow the repository pattern effectively

## WhatsApp Notifications (WasenderAPI)

This project supports sending a WhatsApp message to the admin when a new request is created (manual payment / cash exchange / money exchange).

Set these environment variables in your `.env` (do **not** commit them):

- `WASENDER_ENABLED=true`
- `WASENDER_API_KEY=YOUR_TOKEN_HERE`
- `WASENDER_NOTIFY_TO=9665XXXXXXXX,9627XXXXXXXX` (comma-separated phone numbers, digits only is preferred)
- `WASENDER_NOTIFY_CUSTOMERS=true` (optional, default: true)
- (optional) `WASENDER_BASE_URL=https://www.wasenderapi.com/api`

## Email Notifications (Optional Additional Channel)

You can optionally send **the same notifications** via email in addition to WhatsApp (admins + customers), using Laravel's mail configuration.

Set these environment variables in your `.env` (do **not** commit them):

- `EMAIL_NOTIFY_ENABLED=true` (default: false)
- `EMAIL_NOTIFY_ADMIN=true` (optional, default: true)
- `EMAIL_NOTIFY_CUSTOMERS=true` (optional, default: true)
- `EMAIL_NOTIFY_TO=admin1@example.com,admin2@example.com` (optional, comma-separated admin recipients)

If `EMAIL_NOTIFY_TO` is empty, the app will try to email all admin accounts + the main settings email (if available).


### Frontend Integration
1. **Component Integration:**
    * **_tpl_start.blade.php:** Includes `@yield('pageTitle')` within the `<head>` tag to dynamically set the page title.
    * **_tpl_end.blade.php:** Includes `@stack('css')` at the end of the `<body>` tag to stack additional CSS files.
2. **Usage Example:**
    ```html
    @extends('frontend.includes.site')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @endpush
    @section('title', 'Home Page')

    @section('content')


    @push('js')

    @endpush
    @endsection
