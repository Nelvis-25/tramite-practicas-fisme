<div>
    @vite('resources/css/app.css')

    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <!-- Encabezado con mejor diseño y animación sutil -->
            <div class="mb-8 border-l-4 border-indigo-600 dark:border-indigo-400 pl-4 transition-all duration-300 hover:pl-6">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Gestión de Solicitudes</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Complete el formulario y visualice los datos en tiempo real</p>
            </div>
            
            <!-- Contenedor principal dividido en dos columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Columna 1: Formulario -->
                <div>
                    <!-- Formulario mejorado con efectos de sombra y transiciones -->
                    <form wire:submit.prevent="submit" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
                        <!-- Barra de progreso (opcional) -->
                        <div class="h-1 bg-indigo-100 dark:bg-gray-700">
                            <div class="h-1 bg-indigo-600 dark:bg-indigo-400 w-1/3 transition-all duration-500"></div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Campo Nombre con validación visual -->
                            <div class="mb-4 group">
                                <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Nombre del Proyecto <span class="text-red-500">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        id="nombre" 
                                        name="nombre"
                                        wire:model.defer="nombre"
                                        required
                                        maxlength="600"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white transition-all duration-300"
                                        placeholder="Ingrese el nombre del proyecto"
                                    >
                                </div>
                            </div>
                            
                            <!-- Selector de Estudiante mejorado -->
                            <div class="mb-4 group">
                                <label for="estudiante" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Estudiante <span class="text-red-500">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <select 
                                        id="estudiante" 
                                        name="id_estudiante"
                                        wire:model.defer="id_estudiante"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white appearance-none transition-all duration-300"
                                    >
                                        <option value="">Seleccione un estudiante</option>
                                        <option value="1">Juan Pérez - 20210001</option>
                                        <option value="2">María Gómez - 20210002</option>
                                        <option value="3">Carlos López - 20210003</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selector de Línea de Investigación mejorado -->
                            <div class="mb-4 group">
                                <label for="linea_investigacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        Línea de Investigación <span class="text-red-500">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <select 
                                        id="linea_investigacion" 
                                        name="id_linea_investigacion"
                                        wire:model.defer="id_linea_investigacion"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white appearance-none transition-all duration-300"
                                    >
                                        <option value="">Seleccione una línea</option>
                                        <option value="1">Inteligencia Artificial</option>
                                        <option value="2">Ciencias de la Computación</option>
                                        <option value="3">Ingeniería de Software</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Selector de Asesor mejorado -->
                            <div class="mb-4 group">
                                <label for="asesor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Asesor <span class="text-red-500">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <select 
                                        id="asesor" 
                                        name="id_asesor"
                                        wire:model.defer="id_asesor"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white appearance-none transition-all duration-300"
                                    >
                                        <option value="">Seleccione un asesor</option>
                                        <option value="1">Dr. Roberto Martínez</option>
                                        <option value="2">Dra. Ana Rodríguez</option>
                                        <option value="3">Mg. Luis Sánchez</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Documentos - Diseño compacto -->
                            <div class="mb-4">
                                <div class="flex items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-md font-medium text-gray-800 dark:text-white">Documentos Adjuntos</h3>
                                    <div class="ml-2 h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Solicitud -->
                                    <div class="group">
                                        <label for="solicitud" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                            Solicitud (PDF)
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="file" 
                                                id="solicitud" 
                                                name="solicitud"
                                                wire:model="solicitud"
                                                accept=".pdf"
                                                class="hidden"
                                            >
                                            <label for="solicitud" class="flex items-center justify-center w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-300 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-xs text-gray-500 dark:text-gray-300">Subir archivo</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Constancia -->
                                    <div class="group">
                                        <label for="constancia" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                            Constancia (PDF)
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="file" 
                                                id="constancia" 
                                                name="constancia"
                                                wire:model="constancia"
                                                accept=".pdf"
                                                class="hidden"
                                            >
                                            <label for="constancia" class="flex items-center justify-center w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-300 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-xs text-gray-500 dark:text-gray-300">Subir archivo</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Informe -->
                                    <div class="group">
                                        <label for="informe" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                            Informe (PDF/Word)
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="file" 
                                                id="informe" 
                                                name="informe"
                                                wire:model="informe"
                                                accept=".pdf,.doc,.docx"
                                                class="hidden"
                                            >
                                            <label for="informe" class="flex items-center justify-center w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-300 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-xs text-gray-500 dark:text-gray-300">Subir archivo</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Carta de Presentación -->
                                    <div class="group">
                                        <label for="carta_presentacion" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                            Carta Presentación
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="file" 
                                                id="carta_presentacion" 
                                                name="carta_presentacion"
                                                wire:model="carta_presentacion"
                                                accept=".pdf,.doc,.docx"
                                                class="hidden"
                                            >
                                            <label for="carta_presentacion" class="flex items-center justify-center w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-300 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-xs text-gray-500 dark:text-gray-300">Subir archivo</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Comprobante de pago -->
                                    <div class="group col-span-2">
                                        <label for="comprobante_pago" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                            Comprobante de Pago <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="file" 
                                                id="comprobante_pago" 
                                                name="comprobante_pago"
                                                wire:model="comprobante_pago"
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                class="hidden"
                                                required
                                            >
                                            <label for="comprobante_pago" class="flex items-center justify-center w-full px-3 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-300 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                <span class="text-xs text-gray-500 dark:text-gray-300">Subir comprobante</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estado (nuevo campo) -->
                            <div class="mb-6 group">
                                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 group-focus-within:text-indigo-600 dark:group-focus-within:text-indigo-400 transition-colors">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Estado <span class="text-red-500">*</span>
                                    </div>
                                </label>
                                <div class="relative">
                                    <select 
                                        id="estado" 
                                        name="estado"
                                        wire:model.defer="estado"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white appearance-none transition-all duration-300"
                                    >
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Validado">Validado</option>
                                        <option value="Rechazado">Rechazado</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción con mejores efectos -->
                            <div class="flex justify-end space-x-3">
                                <button 
                                    type="button"
                                    wire:click="cancel" 
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 transition-all duration-300"
                                >
                                    <span class="flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Cancelar
                                    </span>
                                </button>
                                <button 
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
                                >
                                    <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-32 group-hover:h-32 opacity-10"></span>
                                    <span class="relative flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span wire:loading.remove wire:target="submit">Guardar</span>
                                        <span wire:loading wire:target="submit">Guardando...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Columna 2: Vista previa de datos -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Vista de Solicitud
                        </h2>
                    </div>
                    
                    <!-- Vista previa de datos con efecto de actualización en tiempo real -->
                    <div class="p-6">
                        <!-- Tarjeta de info principal -->
                        <div class="mb-8 bg-indigo-50 dark:bg-gray-700 rounded-lg p-4 border-l-4 border-indigo-500 dark:border-indigo-400">
                            <div class="mb-4">
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Sistema de Gestión Académica</h3>
                                <div class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Pendiente
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Estudiante</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" id="estudiante-preview">María Gómez - 20210002</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Asesor</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" id="asesor-preview">Dra. Ana Rodríguez</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Línea de Investigación</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" id="linea-preview">Inteligencia Artificial</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Solicitud</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">03/04/2025</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Documentos adjuntos -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-800 dark:text-white mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Documentos Adjuntos
                            </h3>
                            
                            <div class="space-y-2">
                                <!-- Documento 1 -->
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-200">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Solicitud.pdf</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">245 KB</p>
                                    </div>
                                    <button type="button" class="p-1 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Documento 2 -->
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-200">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Informe_Final.docx</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">1.2 MB</p>
                                    </div>
                                    <button type="button" class="p-1 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Documento 3 -->
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group hover:bg-gray-100 dark:hover:bg-gray-650 transition-all duration-200">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Constancia_Pago.pdf</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">512 KB</p>
                                    </div>
                                    <button type="button" class="p-1 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Línea de tiempo -->
                        <div>
                            <h3 class="text-md font-medium text-gray-800 dark:text-white mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 dark:text-indigo-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Historial de Actividad
                            </h3>
                            
                            <div class="flow-root">
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <li class="py-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                                    <span class="font-medium">Solicitud creada</span>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    03/04/2025 - 10:30 AM
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="py-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                                    <span class="font-medium">Archivos adjuntos agregados</span>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    03/04/2025 - 10:35 AM
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="py-3">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                                    <span class="font-medium">En revisión por el asesor</span>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    03/04/2025 - 11:15 AM
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>