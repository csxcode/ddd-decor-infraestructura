<?php

return [
    'required'              => 'El campo :attribute es requerido.',
    'required_by_action'    => ':attribute es requerido cuando el campo action es :action',
    'not_valid'             => 'El campo :attribute no es valido.',
    'required_list_one'     => 'La lista de :attribute debe tener al menos 1 registro requerido.',
    'not_exists'            => ':attribute no existe.',
    'already_exists'        => ':attribute ya existe.',
    'limit'                 => 'Se ha superado el límite máximo de :attribute',
    'between'               => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file'    => 'El campo :attribute debe estar entre :min y :max kilobytes.',
        'string'  => 'El campo :attribute debe estar entre :min y :max caracteres.',
        'array'   => 'El campo :attribute debe estar entre :min y :max items.',
    ],
    'duplicated'                                    => 'El campo :attribute tiene valores duplicados, verifique ya que estos valores deben ser unicos.',
    'forbidden_create_entity_sb'                    => 'El usuario no puede crear este objeto :entity con la sucursal relacionada',
    'forbidden_entity_sb'                           => 'El usuario no tiene permisos para acceder a este objeto :entity',
    'checklist_cannot_edit_because_does_not_new'    => 'Este checklist no se puede actualizar debido a que su estado ya no es nuevo',
    'checklist_item_photo_does_not_required'        => 'El item :attribute no requiere una foto porque es de tipo CONFORME para este checklist',
    'custom_create_ticket_promotor_status'          => 'Este tipo de usuario promotor no puede crear o actualizar tickets con este estado',
    'ticket_cannot_edit_because_status'             => 'Este ticket ya no se puede actualizar por su estado',
    'forbidden_role_user'                           => 'El rol de este usuario no tiene permisos para realizar esta operacion',
    'forbidden_role_user_by_field'                  => 'El rol de este usuario no tiene permisos para agregar el campo: :attribute',
    'forbidden_role_user_by_object_field'           => 'El rol de este usuario no tiene permisos para agregar :object con :attribute',
    'video_ext_not_allowed'                         => 'Archivo de video no es permitido, solo estas extensiones son validas: :attribute',
    'no_changes'                                    => 'No hay cambios para actualizar',
    'key_not_allowed'                               => 'El campo :attribute no esta permitido.',
    'array_no_one'                                  => 'No hay :attribute para agregar, verifique que los nombres (keys) deben ser correctos',
    'photos_no_one_key'                             => 'No hay fotos para agregar, verifique que los nombres (keys) deben ser correctos',
    'checklist' => [
        'cannot_edit' => 'El checklist ya no puede ser editado',
    ],
    'ticket' => [
        'photos' => [
            'order_duplicated_by_type' => 'El campo :attribute tiene valores duplicados para el tipo :type, verifique ya que estos valores deben ser unicos.',
        ]
    ],
    'wo' => [
        'cannot_edit_because_status' => 'Esta order de trabajo (OT) no se puede actualizar.',
    ],
    'maintenance' => [
        'cannot_update_because_status' => 'Mantenimiento no se puede actualizar debido a su estado.',
        'cannot_delete_because_status' => 'Mantenimiento no se puede eliminar debido a su estado.',
    ],
    'work_order_quote' => [
        'forbidden_because_wo_does_not_quoting' => 'No se puede realizar esta accion debido a que la orden de trabajo (OT) no es de estado cotizando.',
        'vendor_already_exists' => 'Este proveedor ya existe en el presupuesto',
        'vendor_does_not_belong' => 'Este presupuesto no le pertence a este usuario de tipo proveedor.'
    ],
    'work_order_cost_center' =>
    [
        'percente_total_has_exceeded' => 'La suma de todos los centros de costos de esta orden de trabajo ha superado a 100%.'
    ],
    'work_order_history' =>
    [
        'store_wo_is_closed_status_reaperture' => 'La work_order (OT) está [cerrada] por lo tanto solo se permite agregar work_order_history con estado [reapetura].'
    ]
];
