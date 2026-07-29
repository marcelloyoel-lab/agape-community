<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Poster Ibadah Pemuda</title>

    @vite([
        'resources/assets/css/poster.css',
    ])
</head>

<body>
    <main class="poster">
        <div class="poster__date">
            <span class="poster__day">SABTU</span>
            <span class="poster__date-value">25 JULI 2026</span>
            <span class="poster__separator"></span>
            <span class="poster__time">15.00</span>
        </div>

        <div class="timeline" aria-hidden="true">
            <div class="timeline__horizontal"></div>

            <div class="timeline__point timeline__point--mc">
                <span class="timeline__vertical timeline__vertical--down"></span>
            </div>

            <div class="timeline__point timeline__point--firman">
                <span class="timeline__vertical timeline__vertical--up"></span>
            </div>

            <div class="timeline__point timeline__point--music">
                <span class="timeline__vertical timeline__vertical--down"></span>
            </div>

            <div class="timeline__point timeline__point--multimedia">
                <span class="timeline__vertical timeline__vertical--up"></span>
            </div>

            <div class="timeline__arrow"></div>
        </div>

        <section class="assignment assignment--firman">
            <h2 class="assignment__ministry">FIRMAN</h2>

            <ul class="assignment__members">
                <li>Ka Prilly</li>
            </ul>
        </section>

        <section class="assignment assignment--multimedia">
            <h2 class="assignment__ministry">MULTIMEDIA</h2>

            <ul class="assignment__members">
                <li>Yohana</li>
            </ul>
        </section>

        <section class="assignment assignment--mc">
            <h2 class="assignment__ministry">MC</h2>

            <ul class="assignment__members">
                <li>Afanti</li>
            </ul>
        </section>

        <section class="assignment assignment--music">
            <h2 class="assignment__ministry">MUSIK</h2>

            <ul class="assignment__members">
                <li>Cilly</li>
                <li>Mario</li>
            </ul>
        </section>
    </main>
</body>

</html>