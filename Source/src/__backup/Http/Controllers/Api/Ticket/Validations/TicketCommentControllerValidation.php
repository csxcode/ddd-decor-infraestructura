<?php

namespace App\Http\Controllers\Api\Ticket\Validations;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Helpers\UserHelper;
use App\Http\Controllers\Api\Validations\GlobalValidation;
use App\Models\Ticket\TicketStatus;
use App\Models\UserStoreBranch;
use Illuminate\Http\Response;

class TicketCommentControllerValidation
{    
    public static function CreateValidation($request, $resource, $user, $ticket, &$save)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------                     
        $description = ($request->exists('description') ? StringHelper::Trim($request->get('description')) : null) ;

        $save = [            
            'description' => null,           
        ];

        $role = $user->role->name;

        // --------------------------- Ticket ---------------------------
        if (!$ticket) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        } 
                

        // No permitir actualizar si el estado actual del ticket es Cerrado o Cancelado
        if ($ticket->status_id == TicketStatus::TICKET_STATUS_CLOSED || $ticket->status_id == TicketStatus::TICKET_STATUS_CANCELED) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.ticket_cannot_edit_because_status'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource
                ]
            ], Response::HTTP_FORBIDDEN);
        }


         // ------------- Description ---------------------
         if($description == null) {

            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.required', ['attribute' => 'description']),
                'error_code' => ErrorCodesEnum::missing_field,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'description'
                ]
            ], Response::HTTP_BAD_REQUEST);

        }

        $save['description'] = $description;


        // --------------------------- Rules for USER ---------------------------
        // Solo los algunos tipos de usuario pueden actualizar un ticket
        $pass = false;        

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){
           
            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario 
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket->branch_id)
                ->first();
                
            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket->created_by_user));
            $username_from_user = strtolower($user->username);
            
            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            } 
                       
        }    
        
        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }       

        return null;
    }   

    public static function GetValidation($resource, $user, $ticket)
    {
        // --------------------------------------------
        // Get Data From Request
        // --------------------------------------------
        $role = $user->role->name;
        $ticket_clone = clone $ticket;

        if (!$ticket) {
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.not_exists', ['attribute' => 'Ticket']),
                'error_code' => ErrorCodesEnum::not_exists,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'id'
                ]
            ], Response::HTTP_NOT_FOUND);
        }


        // [Check UserStoreBrand Permission ]
        $error_response = GlobalValidation::CheckUserStoreBranchPermission(
            $user, $ticket->branch_id,
            trans('api/validation.forbidden_entity_sb', ['entity' => 'ticket']),
            $resource, 'id');

        if ($error_response)
            return $error_response;

        // ------------------- Rules for USER ---------------------------
        // Solo los algunos tipos de usuario pueden actualizar un ticket
        $pass = false;

        // Visual Usuario (2), cualquier ticket de cualquier sucursal
        if(!$pass){
            if (strtolower($role) == strtolower(UserRoleEnum::VISUAL)) {
                $pass = true;
            }
        }

        // Responsable de Tienda (4), solo si el ticket pertenece a la sucursal que tiene asignado el usuario
        if(!$pass){
        
            if (strtolower($role) == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $check = UserStoreBranch::where('user_id', $user->user_id)
                    ->where('branch_id', $ticket_clone->branch_id)
                    ->first();

                if($check != null){
                    $pass = true;
                }
            }
        }

        // Cualquier tipo de usuario, solo si el ticket pertenece a la sucursal que tiene asignado el usuario 
        // y el ticket haya sido creado por el usuario
        if(!$pass){

            $check = UserStoreBranch::where('user_id', $user->user_id)
                ->where('branch_id', $ticket_clone->branch_id)
                ->first();
                
            $username_from_ticket = strtolower(UserHelper::ExtractUserNameFromString($ticket_clone->created_by_user));
            $username_from_user = strtolower($user->username);
            
            if($check != null && $username_from_ticket == $username_from_user){
                $pass = true;
            } 
                    
        }    
        
        if(!$pass){
            return response()->json([
                'object' => AppObjectNameEnum::ERROR,
                'message' => trans('api/validation.forbidden_role_user'),
                'error_code' => ErrorCodesEnum::forbidden,
                'error_data' => [
                    'resource' => $resource,
                    'field' => 'user'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }       

        return null;
    }   
}