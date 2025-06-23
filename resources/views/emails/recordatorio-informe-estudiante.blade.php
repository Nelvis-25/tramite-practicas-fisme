<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Correo de Recordatorio</title>
</head>
<body>
    <h2>¡Hola, {{ $nombreEstudiante }}!</h2>
    <p>Te recordamos que mañana tienes programada la sustentación de tu <strong>informe de prácticas</strong> en la sala de reuniones de la FISME.</p>
    <p><strong>Fecha:</strong> {{ $fechaHora }}</p>
    <p>Por favor, preséntate puntualmente en la UNTRM.</p>
    <p>Atentamente,<br>{{ $nombreRemitente }}, {{ $cargoRemitente }} aignado para la evaluacion de su informe de  prácticas</p>
</body>
</html>
