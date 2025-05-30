<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Acta de Evaluación</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.39;
            margin: -0.8cm 0.2cm 0.6cm 0.6cm;
        }

        .encabezado {
            border-bottom: 1px solid black;
            padding: 10px 1px;
            width: 97%;
            margin-left: 0;
            
            border-collapse: collapse;
        }
       

        .logo-izquierda {
            width: 90px;
            vertical-align: middle;
        }

        .logo-izquierda img {
            max-height: 70px;
            display: block;
        }

        .texto-derecha {
            text-align: right;
            vertical-align: middle;
    
            font-weight: bold;
            font-size: 9pt;
            line-height: 1.2;
        }

        /* Contenedor del contenido principal con imagen de fondo / marca de agua */

            .section {
                position: relative;
                padding: 2cm 1cm;
                z-index: 1; /* para que esté sobre el fondo */
            }

        .section > * {
                position: relative;
                z-index: 1;
            }



        /* Para centrar el título */
        .center {
            text-align: center;
            font-style: italic;
            font-weight: bold;
            margin: 1em 0;
        }

        /* estilo para negritas */
        .bold {
            font-weight: bold;
        }

        /* firmas */
        .signature-section {
            display: flex;
            justify-content: space-around;
            margin-top: 2em;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-box hr {
            border: 1px solid black;
        }
    </style>
</head>
<body>

    <!-- Encabezado sin fondo -->
    <table class="encabezado">
        <tr>
            <td class="logo-izquierda">
                <img src="{{ public_path('img/logoUNTRM.png') }}" alt="Logo UNTRM" />
            </td>
            <td class="texto-derecha">
                FACULTAD DE INGENIERÍA DE SISTEMAS<br>
                Y MECÁNICA ELÉCTRICA-FISME-BAGUA
            </td>
        </tr>
    </table>

    <!-- Contenido con fondo -->
    <div class="section" style="position: relative; padding: 0cm 1cm;">
            <img src="{{ public_path('img/logoUNTRM2.png') }}" 
         alt="Logo Fondo"
         style="
             position: absolute;
             top: 43%;
             left: 50%;
             width: 420px;
             height: 420px;
             opacity: 0.08;
             transform: translate(-50%, -50%);
             z-index: 0;
         ">
            <h1 style="font-size: 12pt; text-align: center;">
                ACTA DE EVALUACIÓN DE SUSTENTACIÓN DEL INFORME FINAL DE <br>
                PRÁCTICAS PRE PROFESIONALES
            </h1>

        @php
            use Carbon\Carbon;
            \Carbon\Carbon::setLocale('es');
            $fecha = Carbon::parse($informe->fecha_sustentacion);
        @endphp
        <p style="text-align: justify;">
            En la ciudad de Bagua, el día <strong>{{$fecha->day}}</strong> de {{ $fecha->translatedFormat('F') }} del año <strong>{{ $fecha->year }}</strong>, 
            siendo las <strong>{{ $fecha->format('h:i a') }}</strong> horas, el egresado de 
            la Escuela Profesional de Ingeniería de Sistemas <strong>{{ strtoupper($informe->solicitudInforme->estudiante->nombre) }}</strong>, defiende en sesión
             pública y en forma presencial el Informe Final de Prácticas Pre Profesionales 
             titulado: <strong>  {{ ($informe->solicitudInforme->practica->solicitude->nombre ?? 'No hay nombre del informe') }}</strong> ante el Jurado Evaluador, constituido por:</p>

            @php
                $ordenCargos = ['Presidente', 'Secretario', 'Vocal'];
                $juradosOrdenados = [];
                $accesitario = null;

                // Buscar al accesitario
                foreach ($juradosDeLaRonda as $juradoEval) {
                    if ($juradoEval->jurados->cargo === 'Accesitario') {
                        $accesitario = $juradoEval;
                        break;
                    }
                }

                // Para cada cargo esperado, buscar si existe en los jurados
                foreach ($ordenCargos as $cargoBuscado) {
                    $juradoEncontrado = null;
                    foreach ($juradosDeLaRonda as $juradoEval) {
                        if ($juradoEval->jurados->cargo === $cargoBuscado) {
                            $juradoEncontrado = $juradoEval;
                            break;
                        }
                    }

                    // Si encontramos el jurado con ese cargo, lo agregamos
                    if ($juradoEncontrado) {
                        $juradosOrdenados[] = $juradoEncontrado;
                    }
                    // Si no, y hay accesitario disponible, lo usamos como reemplazo
                    elseif ($accesitario) {
                        $juradosOrdenados[] = (object)[
                            'jurados' => (object)[
                                'cargo' => 'Accesitario', // Mostrar como Accesitario
                                'docente' => $accesitario->jurados->docente
                            ]
                        ];
                        $accesitario = null; // Ya fue usado
                    }
                }
            @endphp

            <ul style="list-style-type: none; padding-left: 0;">
                @foreach ($juradosOrdenados as $juradoEval)
                    <li>
                        <strong>{{ $juradoEval->jurados->cargo }}:</strong>
                        {{ $juradoEval->jurados->docente->grado_academico }} {{ $juradoEval->jurados->docente->nombre }}
                    </li>
                @endforeach
            </ul>

        <p style="text-align: justify;">Procedió el estudiante a realizar la exposición 
            del Informe Final de Prácticas Pre Profesionales, haciendo especial mención en 
            el diagnóstico, acciones propuestas, acciones realizadas, resultados obtenidos, 
            dificultades encontradas, conclusiones y recomendaciones. Terminada la defensa 
            del informe final de prácticas pre profesionales presentado, los miembros del 
            Jurado Evaluador pasaron a exponer su opinión sobre el mismo, formulando cuantas
             cuestiones y objeciones oportunas, las cuales fueron contestadas por el aspirante.</p>

        <p style="text-align: justify;">Tras la intervención de los miembros del Jurado Evaluador y las oportunas respuestas
             de la estudiante, el Presidente abre un turno de intervenciones para los 
             presentes en el acto, a fin de que expongan sus opiniones e objeciones que 
             consideren pertinentes.</p>

        <p style="text-align: justify;">Seguidamente, a puerta cerrada, el Jurado Evaluador determinó la calificación 
            global del Informe Final de Prácticas Pre Profesionales en términos de:</p>

                @php
                // Función para convertir número a letras (simple ejemplo)
                        function numeroALetras($num) {
                            $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
                            return ucfirst($formatter->format($num));
                        }

                        $estado = '-';
                        if (is_numeric($promedioRonda)) {
                            $estado = $promedioRonda >= 11 ? 'Aprobado' : 'Desaprobado'; // asumiendo 11 es nota mínima aprobatoria
                        }
                    @endphp

                    <p style="text-align: justify;">
                        En números: ...<strong>{{ $promedioRonda }}</strong>...
                        En Letras: ...{{ is_numeric($promedioRonda) ? numeroALetras($promedioRonda) : '-' }}...
                        <strong>Aprobado({{ $estado === 'Aprobado' ? 'X' : ' ' }})</strong> &nbsp;&nbsp; 
                        <strong>Desaprobado({{ $estado === 'Desaprobado' ? 'X' : ' ' }})</strong>
                    </p> 
        <p style="text-align: justify;">
        Otorgada la calificación, el Secretario del Jurado Evaluador lee la presente Acta en sesión 
        pública. A continuación, se levanta la sesión.
        </p>   
               
        <p style="text-align: justify;">
         Si el estudiante no aprobara la sustentación del Informe Final,  tendrá una última
         oportunidad de sustentarlo en un plazo no mayor de (30) días posteriores a la primera sustentación,
        </p>
        <p style="text-align: justify;">
            Siendo las <strong>{{ $horaActual }}</strong> horas del mismo día, el Jurado Evaluador concluye el acto 
            de sustentación del Informe Final de Prácticas Pre Profesionales.
        </p>
         <br><br> 
                <table style="width: 100%; text-align: center; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        @foreach ($juradosOrdenados as $juradoEval)
                            <td style="width: 33.33%;">
                                <hr style="width: 90%; height: 1px; background-color: black; border: none; margin: 0 auto 5px auto;">
                                <p style="margin: 0; font-size: 10pt; text-transform: uppercase;">
                                    {{ $juradoEval->jurados->cargo }}
                                </p>
                            </td>
                        @endforeach
                    </tr>
                </table>

            <div style="margin-top: 0px; width: 100%; ">
                <p style="font-weight: bold; margin-bottom: 10px; font-size: 10pt;">OBSERVACIONES:</p>
                
                @if(!empty($evaluacion->observacion) && $evaluacion->observacion != 'No hay observaciones registradas')
                   <div style="height: 10px; margin-bottom: 2px;"> {{ $evaluacion->observacion }}</div>
                @else
                    <div style="margin-top: 0px; border-bottom: 1px solid black; height: 10px; margin-bottom: 2px;"></div>
                    <div style="border-bottom: 1px solid black; height: 20px; margin-bottom: 4px;"></div>
                   
                @endif
            </div>

    
    </div>

</body>
</html>
