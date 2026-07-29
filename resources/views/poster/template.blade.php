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

        {{-- Service Date & Time --}}
        <div class="poster__date">
            <span class="poster__day">
                {{ strtoupper($schedule->service_date->locale('id')->translatedFormat('l')) }}
            </span>

            <span class="poster__date-value">
                {{ strtoupper($schedule->service_date->locale('id')->translatedFormat('d F Y')) }}
            </span>

            <span class="poster__separator"></span>

            <span class="poster__time">
                {{ $schedule->service_time->format('H.i') }}
            </span>
        </div>


        {{-- Timeline --}}
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


        {{-- Pelayan Firman --}}
        <section class="assignment assignment--firman">
            <h2 class="assignment__ministry">
                FIRMAN
            </h2>

            <ul class="assignment__members">
                @foreach ($posterData['firman'] as $assignment)
                    <li>
                        {{ $assignment->member->name }}
                    </li>
                @endforeach
            </ul>
        </section>


        {{-- Multimedia --}}
        <section class="assignment assignment--multimedia">
            <h2 class="assignment__ministry">
                MULTIMEDIA
            </h2>

            <ul class="assignment__members">
                @foreach ($posterData['multimedia'] as $assignment)
                    <li>
                        {{ $assignment->member->name }}
                    </li>
                @endforeach
            </ul>
        </section>


        {{-- MC --}}
        <section class="assignment assignment--mc">
            <h2 class="assignment__ministry">
                MC
            </h2>

            <ul class="assignment__members">
                @foreach ($posterData['mc'] as $assignment)
                    <li>
                        {{ $assignment->member->name }}
                    </li>
                @endforeach
            </ul>
        </section>


        {{-- Music --}}
        <section class="assignment assignment--music">
            <h2 class="assignment__ministry">
                MUSIK
            </h2>

            <ul class="assignment__members">
                @foreach ($posterData['music'] as $assignment)
                    <li>
                        {{ $assignment->member->name }}
                    </li>
                @endforeach
            </ul>
        </section>

    </main>
</body>

</html>