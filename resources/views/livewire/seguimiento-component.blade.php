
<div   >
  @vite('resources/css/app.css')
  <div class="max-w-6xl mx-auto p-8 rounded-3xl shadow-2xl bg-white dark:bg-gray-800 transition duration-300 border border-gray-200 dark:border-gray-700">
    
    <h2 class="text-4xl font-extrabold mb-4 text-center text-gray-800 dark:text-white relative">
      <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-blue-500 dark:from-blue-400 dark:to-purple-300">
        @if($nombreSolicitud)
          {{ $nombreSolicitud }}
        @else
          <p class="font-bold">Práctica no precentada</p>
        @endif
      </span>
      <div class="absolute h-1 w-24 bg-gradient-to-r from-blue-500 to-purple-100 bottom-0 left-1/2 transform -translate-x-1/2 mt-2 rounded-full"></div>
    </h2>

    <div class="relative">
      <div class="space-y-5 relative z-10">

        <!-- Línea de tiempo vertical -->
        <div class="absolute left-1/2 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 via-yellow-400 to-green-500 transform -translate-x-1/2 rounded-full z-0"></div>

        <!-- Estado 1: Enviado o Pendiente -->
        <div class="flex items-center justify-start relative">
          <div class="w-1/2 pr-10 text-right transform transition duration-500 hover:scale-105 opacity-{{ empty($fechaCreacion) ? '50' : '100' }}">
              <div class="bg-blue-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-blue-500 dark:border-blue-400 inline-block">
                  <h3 class="text-xl font-bold {{ empty($fechaCreacion) ? 'text-gray-500 dark:text-gray-200' : 'text-blue-600 dark:text-blue-300' }} mb-2">
                      1. Enviado
                  </h3>
      
                  @if(empty($fechaCreacion))
                      <div class="mt-2 flex justify-end">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                              Pendiente
                              <p class="text-gray-600 dark:text-gray-400 ml-2">Solicitud no enviada.</p>
                          </span>
                      </div>
                  @else
                      <p class="text-gray-600 dark:text-gray-200">
                          Tu solicitud fue enviada correctamente el: 
                          <span class="font-bold">{{ $fechaCreacion }}</span>.
                      </p>
                      <div class="mt-2 flex justify-end">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                              Completado
                          </span>
                      </div>
                  @endif
              </div>
          </div>
      
          <div class="relative z-20 flex items-center justify-center w-10 h-10 {{ empty($fechaCreacion) ? 'bg-gray-300 dark:bg-gray-600' : 'bg-blue-500 dark:bg-blue-600' }} rounded-full border-4 border-white dark:border-gray-800 shadow-lg transform transition duration-500 hover:scale-110">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
              </svg>
          </div>
      
          <div class="w-1/2"></div>
      </div>

        <!-- Estado 2: Evaluación de solicitud  -->
        <div class="flex items-center justify-end relative">
          <!-- Círculo indicador -->
          <div class="relative z-20 flex items-center justify-center w-10 h-10
              {{ in_array($estadoSolicitud, ['Validado', 'Rechazado', 'Asignado']) 
                  ? 'bg-green-500 dark:bg-green-600' 
                  : 'bg-gray-300 dark:bg-gray-500' }} 
              rounded-full border-4 border-white dark:border-gray-800 shadow-lg transform translate-x-5 transition duration-500 hover:scale-110">
            @if (in_array($estadoSolicitud, ['Validado', 'Rechazado', 'Asignado']))
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            @else
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            @endif
          </div>

          <!-- Tarjeta de contenido -->
          <div class="w-1/2 pl-10 text-left transform transition duration-500 hover:scale-105 opacity-{{ empty($estadoSolicitud) ? '50' : '100' }}">
            <div class="{{ in_array($estadoSolicitud, ['Validado', 'Asignado']) 
                        ? 'bg-green-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-r-4 border-green-500 dark:border-green-400' 
                        : ($estadoSolicitud === 'Rechazado' 
                            ? 'bg-red-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-r-4 border-red-500 dark:border-red-400' 
                            : 'bg-gray-100 dark:bg-gray-700 p-4 rounded-xl shadow-md border-r-4 border-gray-400 dark:border-gray-500') }}">
              
              <!-- Título -->
              <h3 class="text-xl font-bold mb-2 
                        {{ in_array($estadoSolicitud, ['Validado', 'Asignado']) 
                            ? 'text-green-600 dark:text-green-300' 
                            : ($estadoSolicitud === 'Rechazado' 
                                ? 'text-red-600 dark:text-red-300' 
                                : 'text-gray-500 dark:text-gray-400') }}">
                {{ in_array($estadoSolicitud, ['Validado', 'Asignado']) 
                    ? '2. Solicitud Evaluada' 
                    : ($estadoSolicitud === 'Rechazado' 
                        ? '2. Solicitud Rechazada' 
                        : '2. Solicitud en Revisión') }}
              </h3>

              <!-- Descripción -->
              @if(in_array($estadoSolicitud, ['Validado', 'Rechazado', 'Asignado']))
                <p class="text-gray-600 dark:text-gray-200">
                  Su solicitud ha sido <span class="font-bold lowercase">{{ $estadoSolicitud === 'Asignado' ? 'validado' : strtolower($estadoSolicitud) }}</span>{{ $estadoSolicitud !== 'Rechazado' ? ' con éxito.' : ' Revise el motivo en la sección de observaciones que tiene en su solicitud.' }}
                </p>
              @else
                <div class="mt-2 flex justify-start">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="mr-1 h-2 w-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    Pendiente
                    <p class="text-gray-600 dark:text-gray-300 ml-2">Solicitud pendiente de validación.</p>
                  </span>
                </div>
              @endif

              <!-- Badge de estado (solo cuando hay datos) -->
              @if(in_array($estadoSolicitud, ['Validado', 'Rechazado', 'Asignado']))
                <div class="mt-2 flex justify-start">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ in_array($estadoSolicitud, ['Validado', 'Asignado']) 
                                ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                                : ($estadoSolicitud === 'Rechazado' 
                                    ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' 
                                    : 'bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300') }}">
                    <svg class="mr-1 h-2 w-2 
                              {{ in_array($estadoSolicitud, ['Validado', 'Asignado']) 
                                  ? 'text-green-500 dark:text-green-300' 
                                  : ($estadoSolicitud === 'Rechazado' 
                                      ? 'text-red-500 dark:text-red-300' 
                                      : 'text-gray-500 dark:text-gray-400') }}" 
                        fill="currentColor" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    Completado
                  </span>
                </div>
              @endif
            </div>
          </div>
        </div>
        
        <!-- Estado 3: Lista de Jurados -->
          <div class="flex items-center justify-start relative">
            <div class="w-1/2 pr-10 text-right transform transition duration-500 hover:scale-105 opacity-{{ empty($jurados) ? '50' : '100' }}">
                <div class="bg-blue-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-l-4 border-blue-500 dark:border-blue-400 inline-block">
                    
                    <h3 class="text-xl font-bold mb-2 {{ empty($jurados) ? 'text-gray-500 dark:text-gray-400' : 'text-blue-600 dark:text-blue-400' }}">
                        3. Jurados encargados de evaluar su plan de prácticas:
                    </h3>

                    @if(empty($jurados))
                    <div class="mt-2 flex justify-start">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                        <svg class="mr-1 h-2 w-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                          <circle cx="4" cy="4" r="3" />
                        </svg>
                        Pendiente
                        <p class="text-gray-600 dark:text-gray-300 ml-2">Aun no se han asignado jurados.</p>
                      </span>
                    </div>
                    @else
                        <ul class="text-gray-700 dark:text-gray-100 list-disc ml-4">
                            @foreach($jurados as $jurado)
                                <li>
                                    <strong class="text-gray-900 dark:text-white">{{ $jurado['cargo'] }}:</strong>
                                    <span class="text-gray-700 dark:text-gray-200">{{ $jurado['nombre'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-2 flex justify-end">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-800 dark:bg-blue-700 dark:text-blue-100">
                                Completado
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="relative z-20 flex items-center justify-center w-10 h-10 {{ empty($jurados) ? 'bg-gray-300 dark:bg-gray-600' : 'bg-blue-500 dark:bg-blue-600' }} rounded-full border-4 border-white dark:border-gray-800 shadow-lg transform transition duration-500 hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                </svg>
            </div>

            <div class="w-1/2"></div>
          </div>
         
        
        <!-- Estado 4: Fecha de Sustentación - Versión Mejorada -->
        <div class="flex items-center justify-end relative">
          <!-- Espacio vacío izquierdo -->
          <div class="w-1/2"></div>
        
          <!-- Círculo indicador -->
          <div class="relative z-20 flex items-center justify-center w-10 h-10 
              {{ $observaciones ? 'bg-green-500 dark:bg-green-600' : 'bg-gray-300 dark:bg-gray-600' }} 
              rounded-full border-4 border-white dark:border-gray-800 shadow-lg transform translate-x-1 transition duration-500 hover:scale-110">
        
            @if($observaciones)
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            @else
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            @endif
          </div>
        
          <!-- Contenido -->
          <div class="w-1/2 pl-10 text-left transform transition duration-500 hover:scale-105 opacity-{{ $observaciones ? '100' : '50' }}">
            <div class="{{ $observaciones 
                        ? 'bg-green-50 dark:bg-gray-800 p-4 rounded-xl shadow-md border-r-4 border-green-500 dark:border-green-400' 
                        : 'bg-gray-100 dark:bg-gray-700 p-4 rounded-xl shadow-md border-r-4 border-gray-400 dark:border-gray-500' }}">
        
              <h3 class="text-xl font-bold mb-2 {{ $observaciones ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                4. Fecha de sustentación
              </h3>
        
              @if($observaciones === 'Por sustentar')
                <p class="text-gray-700 dark:text-gray-200 mb-3">
                  La sustentación está programada para el <strong>{{ $fechaSustentacion }}</strong> en la sala de reuniones de la FISME. El practicante debe presentar diapositivas para exponer su plan de prácticas.
                </p>
        
              @elseif($observaciones === 'Sustentado')
                <p class="text-gray-700 dark:text-gray-200 mb-3">
                   Usted a <strong>{{ strtolower($observaciones) }}</strong> su plan de prácticas el <strong>{{ $fechaSustentacion }}</strong> y fue <strong>{{ $estadoPlan }}</strong> por la comsión permanente.
                </p>
        
              @elseif($observaciones)
                <p class="text-gray-700 dark:text-gray-200 mb-3">
                 Estimado practicante para comunicarlo que la sustentación fue <strong>{{ $observaciones }}</strong>
                </p>
        
              @else
                <p class="text-gray-600 dark:text-gray-300 mb-3 flex items-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 mr-2">
                    <svg class="mr-1 h-2 w-2 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                      <circle cx="4" cy="4" r="3" />
                    </svg>
                    Pendiente
                  </span>
                  Aún no se ha programado la fecha de la sustentación.
                </p>
              @endif
        
              @if($observaciones)
                <div class="flex justify-start mt-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Completado
                  </span>
                </div>
              @endif
        
            </div>
          </div>
        </div>
        
      
        </div>
    </div>
      
      <!-- Footer con información adicional -->
      <div class="mt-16 pt-8 border-t border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            <p>Si tienes alguna duda, contacta a la <span class="font-medium text-blue-600 dark:text-blue-400">Oficina de Prácticas</span>.</p>
          </div>
          <div class="flex space-x-4">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
              Descargar PDF
            </button>
            <button class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-white font-medium rounded-lg transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
              Ver detalles
            </button>
          </div>
        </div>
      </div>
      
      <!-- Modo oscuro toggle -->
      <div class="mt-6 text-center">
        <button id="darkModeToggle" class="inline-flex items-center justify-center p-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>
      </div>
    </div>
    
    <script>
      // Toggle dark mode
      document.getElementById('darkModeToggle').addEventListener('click', function() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
      });
      
      // Check for saved dark/light mode preference
      if (localStorage.getItem('darkMode') === 'true' || 
          (localStorage.getItem('darkMode') === null && 
           window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    </script>
</div>
