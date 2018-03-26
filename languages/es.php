<?php

return [
	'answers' => 'Respuestas',
	'answers:addyours' => 'Comparte tu respuesta',

	/**
	 * General stuff
	 */
	'item:object:answer' => "Respuestas",
	'item:object:question' => "Preguntas",

	// admin
	'admin:upgrades:set_question_status' => "Definir estado para todas las preguntas",
	'admin:upgrades:set_question_status:description' => "Asegurarse de que todas las preguntas tengas un campo de estado en sus metadatos. Las preguntas viejas no tienen en esto por defecto.",

	/**
	 * Menu items
	 */
	'questions:menu:user_hover:make_expert' => "Hacer experto en Preguntas",
	'questions:menu:user_hover:make_expert:confirm' => "Estas seguro de que quieres marcar este usuario como experto en Preguntas de %s?",
	'questions:menu:user_hover:remove_expert' => "Quitar experto en Preguntas",
	'questions:menu:user_hover:remove_expert:confirm' => "Estas seguro de que quieres quitar este usuario como experto en Preguntas de %s?",

	'questions:menu:entity:answer:mark' => "Marcar como correcta",
	'questions:menu:entity:answer:unmark' => "Desmarcar como correcta",

	'questions:menu:filter:todo' => "Pendiente",
	'questions:menu:filter:todo_group' => "Pendiente del grupo",
	'questions:menu:filter:experts' => "Expertos",
	'questions:menu:filter:tags' => "Etiquetas: %s",

	'river:create:object:question' => '%s preguntó %s',
	'river:create:object:answer' => '%s compartió una respuesta a la pregunta %s',

	'questions' => 'Preguntas',
	'questions:asked' => 'Preguntada por %s',
	'questions:answered' => 'Última respuesta de %s %s',
	'questions:answered:correct' => 'Repuesta correcta provista por %s %s',

	'questions:everyone' => 'Todas las Preguntas',
	'questions:add' => 'Agregar una Pregunta',
	'questions:todo' => 'Pendientes',
	'questions:todo:none' => 'No hay pendientes, excelente trabajo!',
	'questions:owner' => "Preguntas de %s",
	'questions:none' => "No se han realizado preguntas aun.",
	'questions:group' => 'Preguntas en el grupo',
	'questions:enable' => 'Habilitar preguntas en el grupo',

	'questions:edit:question:title' => 'Pregunta',
	'questions:edit:question:description' => "Detalles",
	'questions:edit:question:container' => "Dónde debería ser listada esta pregunta",
	'questions:edit:question:container:select' => "Elige un grupo",
	'questions:edit:question:move_to_discussions' => "Mover a discusiones",
	'questions:edit:question:move_to_discussions:confirm' => "Estás seguro de querer convertir esta Pregunta en una Discusión? Esto no puede deshacerse!!",

	'questions:object:answer:title' => "Respuesta a la pregunta %s",

	/**
	 * experts page
	 */
	'questions:experts:title' => "Expertos",
	'questions:experts:none' => "Ningún experto fue asignado aun a la pregunta %s.",
	'questions:experts:description:group' => "Abajo esta el listado de usuarios expertos en el grupo %s. Estas personas asisten con respuestas a las preguntas.",
	'questions:experts:description:site' => "Abajo esta el listado de usuarios expertos. Estas personas asisten con respuestas a las preguntas.",

	/**
	 * notifications
	 */
	'questions:notifications:create:subject' => "Han publicado una nueva pregunta",
	'questions:notifications:create:summary' => "Han publicado una nueva pregunta",
	'questions:notifications:create:message' => "Hola %s

Han publicado la siguiente pregunta: %s.

Para responderla ingresa a:
%s",
	'questions:notifications:move:subject' => "Una pregunta fue movida",
	'questions:notifications:move:summary' => "Una pregunta fue movida",
	'questions:notifications:move:message' => "Hola %s

La pregunta: %s ha sido movida, por lo que tendrás que responderla.

Para responderla ingresa a:
%s",

	'questions:notifications:answer:create:subject' => "Hay una respuesta para la pregunta: %s",
	'questions:notifications:answer:create:summary' => "Hay una respuesta para la pregunta: %s",
	'questions:notifications:answer:create:message' => "Hola %s

%s publicó una respuesta a la pregunta '%s'.

%s

Para ver la respuesta ingresa en:
%s",
	'questions:notifications:answer:correct:subject' => "Una respuesta fue marcada como correcta para la pregunta %s",
	'questions:notifications:answer:correct:summary' => "Una respuesta fue marcada como correcta para la pregunta %s",
	'questions:notifications:answer:correct:message' => "Hola %s

%s marcó una respuesta como correcta para la pregunta '%s'.

%s

Para ver la respuesta ingresa en:
%s",
	'questions:notifications:answer:comment:subject' => "Nuevo comentario en una respuesta",
	'questions:notifications:answer:comment:summary' => "Nuevo comentario en una respuesta",
	'questions:notifications:answer:comment:message' => "Hola %s

%s hizo un comentario en una respuesta a la pregunta '%s'.

%s

Para ver el comentario ingresa en:
%s",

	'questions:daily:notification:subject' => "Resumen diario de preguntas a responder",
	'questions:daily:notification:message:more' => "Ver mas",
	'questions:daily:notification:message:overdue' => "Las siguientes preguntas estás atrasdas",
	'questions:daily:notification:message:due' => "Las siguientes preguntas necesitan ser respondidas hoy",
	'questions:daily:notification:message:new' => "Nuevas preguntas",

	'questions:notification:auto_close:subject' => "La pregunta '%s' se cerró por inactividad",
	'questions:notification:auto_close:summary' => "La pregunta '%s' se cerró por inactividad",
	'questions:notification:auto_close:message' => "Hola %s,

tu pregunta '%s' ha estado inactiva por %s días. Por esa razón, la misma ha sido cerrada y ya no recibirá respuestas.

Para ver la pregunta, ingresa en:
%s",

	/**
	 * answers
	 */
	'questions:answer:edit' => "Editar respuesta",
	'questions:answer:checkmark:title' => "%s marcó esta respuesta como la correcta para la pregunta %s",

	'questions:search:answer:title' => "Respuesta",

	/**
	 * plugin settings
	 */
	'questions:settings:general:title' => "Cofiguración general",
	'questions:settings:general:close' => "Cerrar pregunta cuando una respuesta sea marcada como correcta",
	'questions:settings:general:close:description' => "Cuando la pregunta tenga una respuesta marcada como correcta ya no podrán publicarse mas respuestas.",
	'questions:settings:general:solution_time' => "Definir el tiempo límite en días para que una pregunta este solucionada.",
	'questions:settings:general:solution_time:description' => "Las preguntas deberán ser respondidas antes del tiempo límite. Los grupos pueden definir su propio límite. Poner en 0 para que no haya límite.",
	'questions:settings:general:auto_close_time' => "Cerrar preguntas automaticamente después de una cantidad de días.",
	'questions:settings:general:auto_close_time:description' => "Las preguntas que no estén resueltas en esta cantidad de días se cerrarán automaticamente. Poner en 0 para deshabilitar el cerrado automático.",
	'questions:settings:general:solution_time_group' => "Los dueños de un grupo pueden definir su popio tiempo límite.",
	'questions:settings:general:solution_time_group:description' => "Si no se permite, los grupos usaran el tiempo límite definido por defecto mas arriba.",
	'questions:settings:general:limit_to_groups' => "Solo es posible hacer preguntar en grupos",
	'questions:settings:general:limit_to_groups:description' => "Si se activa, no es posible hacer preguntas fuera de los grupos",

	'questions:settings:experts:title' => "Configuración de expertos",
	'questions:settings:experts:enable' => "Habilitar rol de expertos",
	'questions:settings:experts:enable:description' => "Los expertos tienen tienen privilegios especiles y pueden ser asignados por los administradores y los miembros de los grupos.",
	'questions:settings:experts:answer' => "Solamente los expertos pueden responder preguntas",
	'questions:settings:experts:mark' => "Solamente los expertos pueden marcar una respuesta como correcta",

	'questions:settings:access:title' => "Configuración de acceso",
	'questions:settings:access:personal' => "Cuál sera el nivel de acceso para las preguntas personales",
	'questions:settings:access:group' => "Cuál sera el nivel de acceso para las preguntas en grupos",
	'questions:settings:access:options:user' => "Definido por el usuario",
	'questions:settings:access:options:group' => "Sólo miembros del grupo",

	/**
	 * group settings
	 */
	'questions:group_settings:title' => "Configuración de preguntas",

	'questions:group_settings:solution_time:description' => "Las preguntas deben ser respondidas antes de este tiempo límite (expresado en días). Poner en 0 para que no haya límite.",

	'questions:group_settings:who_can_ask' => "Quién puede publicar preguntas en este grupo",
	'questions:group_settings:who_can_ask:members' => "Todos los miembros",
	'questions:group_settings:who_can_ask:experts' => "Solo los expertos",

	'questions:group_settings:who_can_answer' => "Quién puede responder preguntas en este grupo",
	'questions:group_settings:who_can_answer:experts_only' => "El administrador del sitio definió que únicamente los expertos pueden responder.",

	'questions:group_settings:auto_mark_correct' => "Cuando un experto publique una respuesta, marcarla automaticamente como la correcta.",

	/**
	 * Widgets
	 */

	'widget:questions:title' => "Preguntas",
	'widget:questions:description' => "Puedes ver el estado de tus preguntas.",
	'widget:questions:content_type' => "Qué preguntas mostrar",
	'widget:questions:more' => "Ver mas preguntas",

	/**
	 * Actions
	 */

	'questions:action:answer:save:error:container' => "No tienes permiso para responder esta pregunta!",
	'questions:action:answer:save:error:body' => "Es necesario un cuerpo de texto: %s, %s",
	'questions:action:answer:save:error:save' => "Hubo un problema al guardar tu respuesta!",
	'questions:action:answer:save:error:question_closed' => "La pregunta que quieres responder esta cerrada",

	'questions:action:answer:toggle_mark:error:not_allowed' => "No tienes permiso para marcar una respuesta como la correcta",
	'questions:action:answer:toggle_mark:error:duplicate' => "Ya existe una respuesta correcta para esta pregunta",
	'questions:action:answer:toggle_mark:success:mark' => "La respuesta ha sido marcada como la correcta",
	'questions:action:answer:toggle_mark:success:unmark' => "La respuesta ya no está marcada como la correcta",

	'questions:action:question:save:error:container' => "No tienes permiso para publicar preguntas aquí",
	'questions:action:question:save:error:body' => "Es necesario una pregunta y una descripción: %s, %s",
	'questions:action:question:save:error:save' => "Hubo un problema al guardar tu pregunta!",
	'questions:action:question:save:error:limited_to_groups' => "Las preguntas solo pueden hacerse dentro de un grupo, por favor elige un grupo",

	'questions:action:question:move_to_discussions:error:move' => "No tienes permiso para convertir una pragunta en discusión",
	'questions:action:question:move_to_discussions:error:topic' => "Hubo un error al guardar el tema de discusión, intenta de nuevo",
	'questions:action:question:move_to_discussions:success' => "La pregunta fue convertida en en un tema de discusión",

	'questions:action:toggle_expert:success:make' => "%s es una pregunta para el experto %s",
	'questions:action:toggle_expert:success:remove' => "%s ya no es una pregunta para el experto %s",

	'questions:action:group_settings:success' => "La configuración del grupo fue guardada",
];
