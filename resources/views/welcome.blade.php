<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

        <style>
            html, body {
                height: 100%;
                margin: 0;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgb(0 41 68 / var(--tw-bg-opacity, 1));
            }

            img {
                max-width: 320px;
                width: 60%;
                height: auto;
            }
        </link>
    </head>
    <body>
        <img
            src="{{ asset('images/logo-atlas.png') }}"
            alt="Universo Atlas"
        >
    </body>
</html>
