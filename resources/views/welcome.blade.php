<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Blur Index - Added by Jay -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('business-info.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Source Sans Pro';
        }

        .video-background {
            pointer-events: none;
            background: #000;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: -99;
        }

        .video-foreground,
        .video-background iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        #vidtop-content {
            top: 0;
            color: #fff;
        }

        .vid-info {
            position: absolute;
            top: 0;
            right: 0;
            width: 33%;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            padding: 1rem;
            font-family: Avenir, Helvetica, sans-serif;
        }

        .vid-info h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 0;
            line-height: 1.2;
        }

        .vid-info a {
            display: block;
            color: #fff;
            text-decoration: none;
            background: rgba(0, 0, 0, 0.5);
            transition: .6s background;
            border-bottom: none;
            margin: 1rem auto;
            text-align: center;
        }

        @media (min-aspect-ratio: 16/9) {
            .video-foreground {
                height: 300%;
                top: -100%;
            }
        }

        @media (max-aspect-ratio: 16/9) {
            .video-foreground {
                width: 300%;
                left: -100%;
            }
        }

        @media all and (max-width: 600px) {
            .vid-info {
                width: 50%;
                padding: .5rem;
            }

            .vid-info h1 {
                margin-bottom: .2rem;
            }
        }

        @media all and (max-width: 500px) {
            .vid-info .acronym {
                display: none;
            }
        }

        body{
            padding: 0;
            margin: 0;
        }

        nav{
            width: 100%;
            color: white;
            display: flex;
            margin-top: 40px;
            justify-content: center;
        }

        .blur-bg{
            height: 100%;
            width: 80%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: space-between;
            border-radius: 10px;
            overflow: hidden;
        }


        /* if backdrop support: very transparent and blurred */
        @supports ((-webkit-backdrop-filter: none) or (backdrop-filter: none)) {
            .blur-bg{
                background: rgba(0, 0, 0 ,0);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
            }
        }

        .ele1{
            display: flex;
            padding: 25px;
            margin: auto 0;
            font-weight: bold;
            font-size: larger;
            width: auto;
        }
        .ele2{
            display: flex;
            width: auto;
            cursor: pointer;
        }
        .ele2:hover{
            background: rgba(0, 0, 0, 0.25);
        }
        .ele2:active{
            background: rgba(0, 0, 0, 0.5);
        }

        .link{
            padding: 28px;
            text-decoration: none;
            color: white;
        }
    </style>

</head>

<body>
    <nav>
        <div class="blur-bg">
            <div class="ele1">{{ config('business-info.erp_name') }}</div>
            <div class="ele2">
                <a class="link" href="/login">Login</a>
            </div>
        </div>
    </nav>
    <div class="video-background">
        <div class="video-foreground">
            <iframe
                src="https://www.youtube.com/embed/X8pxog8f4RY?controls=0&amp;modestbranding?showinfo=1&amp;rel=0&amp;autoplay=1&amp;mute=1&amp;playlist=X8pxog8f4RY&amp;loop=1"
                frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</body>

</html>
