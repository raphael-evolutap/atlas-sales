<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
        </style>
    </head>
    <body>
        <img
            src="{{ asset('images/logo-atlas.png') }}"
            alt="{{ config('app.name', 'Atlas') }}"
        >
    </body>
</html>
