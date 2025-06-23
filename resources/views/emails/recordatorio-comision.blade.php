<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recordatorio de Sustentaciones</title>
</head>
<body>
    <h2>¡Hola, {{ $nombreIntegrante }} ({{ $cargoIntegrante }}) de la Comisión Permanente!</h2>
    <p>Te recordamos que mañana tienes programadas las siguientes sustentaciones de planes de práctica en la sala de reuniones de la FISME:</p>

    <ul>
        @foreach ($sustentaciones as $sustentacion)
            <li><strong>{{ $sustentacion['estudiante'] }}</strong> a las {{ \Carbon\Carbon::parse($sustentacion['fecha'])->format('H:i') }} ({{ \Carbon\Carbon::parse($sustentacion['fecha'])->format('d/m/Y') }})</li>
        @endforeach
    </ul>

    <p>Por favor, preséntate puntualmente en la sala de reunión de la FISME.</p>

</body>
</html>
