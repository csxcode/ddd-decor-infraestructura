<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Enums\AppObjectNameEnum;
use App\Enums\ErrorCodesEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\Base64Helper;
use App\Helpers\JsonHelper;
use App\Helpers\StringHelper;
use App\Http\Controllers\Api\Ticket\Validations\TicketControllerValidation;
use App\Http\Controllers\Controller;
use App\Models\Ticket\Ticket;
use App\Models\Ticket\TicketPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response as ResponseFacades;

class TicketPhotoController extends Controller
{
    protected $resource = AppObjectNameEnum::TICKET_PHOTO;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function AddPhotos($id)
    {
        // Check if json is valid
        if(!$this->request->json()->all())
            return JsonHelper::ReturnResponseJsonInValid();

        try {
            $user = User::GetByToken($this->request->bearerToken());
            $ticket = Ticket::where('id', $id)->first();
            $photos_data = [];

            // --------------------------------------------
            // Validations
            // --------------------------------------------
            $error_response = TicketControllerValidation::AddPhotosValidation($this->request, $this->resource, $user, $ticket, $photos_data);

            if($error_response)
                return $error_response;

            // Check if the folder is already created otherwise will be create
            $folder_path = Config::get('app.path_ticket_photos') . $ticket->id . '/';
            if (!file_exists($folder_path)) {
                mkdir($folder_path, 0777, true);
            }


            // --------------------------------------------
            // Save
            // --------------------------------------------
            DB::beginTransaction();

            $exclude_guids = [];


            // [TicketPhoto]
            foreach($photos_data['photos'] as $photo) {

                if($photo['new']){
                    TicketPhoto::create([
                        'ticket_id' => $ticket->id,
                        'guid' => $photo['guid'],
                        'name' => $photo['name'],
                        'order' => $photo['order'],
                        'type' => $photo['type'],
                    ]);

                    // Save image to disk
                    $safeName = $photo['guid'] . '.' . $photo['extension'];
                    file_put_contents($folder_path . $safeName, base64_decode($photo['photo']));
                }

                // Exclude id so that it does not delete
                array_push($exclude_guids, $photo['guid']);
            }

            // delete photos excluding ids
            TicketPhoto::where('ticket_id', $ticket->id)
                ->whereNotIn('guid', $exclude_guids)
                ->forceDelete();


            DB::commit();

            // --------------------------------------------
            // Return Data
            // --------------------------------------------
            return response()->json(
                [
                    'object' => AppObjectNameEnum::TICKET_PHOTO,
                    'ticket_id' => $ticket->id
                ],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ErrorHelper::SendInternalErrorMessageForApi($e);
        }

    }

}
