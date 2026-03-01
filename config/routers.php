<?php
return [

  // 🏠 Panel principal
  'dashboard' => [
    'vista' => 'dashboard/dashboard',
    'titulo' => 'Panel Principal'
  ],

  // 🏫 Institución
  'institucion/index' => [
    'vista' => 'autenticacion/views/institucion/index',
    'titulo' => 'Datos de la Institución'
  ],
  'institucion/matricula/secciones' => [
    'vista' => 'institucion/matricula/views/index_secciones',
    'titulo' => 'Secciones de la Institución'
  ],
  'institucion/consultar' => [
    'vista' => 'institucion/views/consultar',
    'titulo' => 'Consulta de Institución'
  ],

  // 📋 Matrícula
  'historial_matricula' => [
    'vista' => 'matricula/views/historial_matricula',
    'titulo' => 'Historial de Matrícula'
  ],
  'inscripcion' => [
    'vista' => 'matricula/views/inscripcion',
    'titulo' => 'Inscripción Académica'
  ],
  'formulario_reinscribir' => [
    'vista' => 'matricula/views/formulario_reinscribir',
    'titulo' => 'Reinscripción Estudiantil'
  ],
  'crear_seccion' => [ 
    'vista' => 'institucion/matricula/views/crear_seccion',
    'titulo' => 'Nueva Sección Académica'
  ],

  // 👨‍🎓 Estudiantes
  'estudiantes' => [
    'vista' => 'autenticacion/views/estudiantes/estudiantes',
    'titulo' => 'Gestión de Estudiantes'
  ],
  'editar_estudiante' => [
    'vista' => 'estudiantes/views/editar_estudiante',
    'titulo' => 'Editar Estudiante'
  ],
  'ficha_estudiante' => [
    'vista' => 'estudiantes/views/ficha_estudiante',
    'titulo' => 'Ficha del Estudiante'
  ],
  'estudiante/nuevo' => [
    'vista' =>  'autenticacion/views/estudiantes/registrar_estudiante',
    'titulo' => 'Registrar Estudiante'
  ],
'estudiante/completar' => [
  'vista' => 'autenticacion/views/estudiantes/completar_datos',
  'titulo' => 'Completar Datos del Estudiante'
],

  // Representante
  'representante/ficha' => [
    'vista'  => 'representante/views/ficha',
    'titulo' => 'Ficha del Representante',
    'roles'  => ['admin', 'registro'] // ajusta según tus roles
  ],


  // 📑 Validación
  'validacion' => [
    'vista' => 'validacion/views/index',
    'titulo' => 'Validación Documentos'
  ],

  // 📘 Académico
  'academico' => [
    'vista' => 'autenticacion/views/academico/planes/index',
    'titulo' => 'Planes de Estudio'
  ],
  'planes/editar_asignaturas' => [
    'vista' => 'estructura_plan/views/editar_asignaturas',
    'titulo' => 'Editar Asignaturas',
    'roles' => ['admin']
  ],
  'planes/eliminar_asignatura' => [
    'vista' => 'estructura_plan/views/eliminar_asignatura',
    'titulo' => 'Eliminar Asignaturas',
    'roles' => ['admin']
  ],

  // 🆕 Nuevo Plan de Estudio
  'planes/nuevo' => [
    'vista' => 'autenticacion/views/academico/planes/crear_planestudio',  
    'titulo' => 'Nuevo Plan de Estudio'
  ],
  'planes/catalogo' => [
    'vista' => 'autenticacion/views/academico/planes/catalogo_plan',
    'titulo' => 'Catálogo de Asignaturas'
  ],


  // 🆕 Configuración

  'configuracion' => [
    'vista' => 'autenticacion/views/configuracion/indexAdmin',
    'titulo' => 'Configuración del Sistema'
  ],

  'configuracion/permisologia' => [
    'vista' => 'autenticacion/views/configuracion/permisologia/asignar_permisos',
    'titulo' => 'Configuración del Sistema'
  ],
   'configuracion/controlPermisos' => [
    'vista' => 'autenticacion/views/configuracion/permisologia/mostrarpermisos',
    'titulo' => 'Configuración del Sistema'
  ],
  // 🚫 Error
  'error/404' => [
    'vista' => 'error/views/404',
    'titulo' => 'Página no encontrada'
  ]

];
