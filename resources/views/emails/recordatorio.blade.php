<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Correo de Recordatoria</title>
</head>
<body>
    <h2>¡Hola, {{ $nombreEstudiante }}!</h2>
    <p>Te recordamos que mañana tienes programada tu sustentación de plan de prácticas en la sala de reuniones de la FISME.</p>
    <p><strong>Fecha:</strong> {{ $fechaHora }}</p>
    <p>Por favor, preséntate puntualmente en la UNTRM.</p>
    <p>Atentamente,<br>{{ $nombreRemitente }}, {{ $cargoRemitente }} de la Comisión Permanente</p>
</body>
</html>
