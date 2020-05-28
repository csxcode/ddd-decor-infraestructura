<?php

namespace App\Models\Ticket;

use App\Enums\UserRoleEnum;
use App\Helpers\ErrorHelper;
use App\Helpers\FileHelper;
use App\Helpers\FunctionHelper;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\TicketSearch;
use App\Models\Views\vEquivalencesChecklistTicketType;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $table = 'ticket';
    protected $guarded = ['id'];

    public static function GenerateNumber()
    {
        $ini_counter = 1000;
        $max = Ticket::max('ticket_number');
        if (!$max)
            $max = $ini_counter;
        $max = ((int) $max + 1);
        return $max;
    }

    public static function CreateTicketsFromChecklist($checklist, $user)
    {

        // $user = the user that created the checklist otherwise if it does not exists then the current user

        $items_for_generate_ticket = DB::table('checklist_item_details')->where('checklist_id', $checklist->id)->where('disagreement_generate_ticket', 1)->get();

        if (count($items_for_generate_ticket) > 0) {

            $checklist_item_ids = $items_for_generate_ticket->pluck('checklist_item_id');
            $equivalences = vEquivalencesChecklistTicketType::GetEquivalences($checklist_item_ids);

            if (count($equivalences) > 0) {

                $exhibition = DB::table('exhibition')->where('exhibition_id', $checklist->exhibition_id)->first();

                foreach ($equivalences as $checklist_type_id => $checklist_types) {

                    foreach ($checklist_types as $ticket_type_id => $checklist_items) {

                        $description = null;
                        $photos = array();

                        // Ini: Build Description and Get/Set Photos
                        foreach ($checklist_items as $item) {

                            $checklist_item_detail = $items_for_generate_ticket->filter(function ($data) use ($item) {
                                return $data->checklist_item_id == $item->checklist_item_id;
                            })->first();


                            $description .= ($description == null ? "" : "\n\n") .
                                $item->checklist_type_name . ": " . $item->checklist_item_name . "\n" .
                                $checklist_item_detail->disagreement_reason;

                            // check if there is photo
                            if ($checklist_item_detail->disagreement_photo_guid) {
                                $add_photo = [
                                    'guid_ticket' => FunctionHelper::CreateGUID(16),
                                    'guid_checklist' => $checklist_item_detail->disagreement_photo_guid,
                                    'name' => $checklist_item_detail->disagreement_photo_name,
                                    'extension' => FileHelper::GetExtensionFromFilename($checklist_item_detail->disagreement_photo_name)
                                ];

                                array_push($photos, $add_photo);
                            }
                        }
                        // End: Build Description and Get/Set Photos


                        // Add Ticket
                        $ticket = Ticket::create([
                            'ticket_number' => Ticket::GenerateNumber(),
                            'status_id' => TicketStatus::TICKET_STATUS_NEW,
                            'type_id' => $ticket_type_id,
                            'exhibition_id' => $checklist->exhibition_id,
                            'branch_id' => $exhibition->branch_id,
                            'description' => $description,
                            'subtype_id' => $item->ticket_type_sub_id,
                            'created_at' => Carbon::now(),
                            'created_by_user' => User::GetCreatedByUser($user),
                            'updated_at' => null
                        ]);

                        // Add Photos
                        if (count($photos) > 0) {

                            $folder_path_checklist = Config::get('app.path_checklist_photos') . $checklist->id . '/';

                            // Check if the folder is already created otherwise will be create
                            $folder_path_ticket = Config::get('app.path_ticket_photos') . $ticket->id . '/';
                            if (!file_exists($folder_path_ticket)) {
                                mkdir($folder_path_ticket, 0777, true);
                            }

                            $counter_photo = 0;

                            foreach ($photos as $photo) {

                                $counter_photo += 1;

                                TicketPhoto::create([
                                    'ticket_id' => $ticket->id,
                                    'guid' => $photo['guid_ticket'],
                                    'name' => $photo['name'],
                                    'order' => $counter_photo
                                ]);

                                // Copy image from checklist to ticket folder
                                $path_photo_checklist = $folder_path_checklist . $photo['guid_checklist'] . '.' . $photo['extension'];
                                $path_photo_ticket = $folder_path_ticket . $photo['guid_ticket'] . '.' . $photo['extension'];

                                if (!copy($path_photo_checklist, $path_photo_ticket)) {
                                    $error = 'Image can not copy, $path_photo_checklist: [' . $path_photo_checklist . '], $path_photo_ticket: [' . $path_photo_ticket . ']';
                                    throw new \Exception($error);
                                }
                            }
                        }


                        // Send Emails
                        try {
                            Ticket::SendEmailNewTicket($ticket->id, $user);
                        } catch (\Exception $e) {
                            ErrorHelper::SendInternalErrorMessageForApi($e);
                        }
                    }
                }
            }
        }
    }

    public static function GetImageURL($ticket_id, $photo_guid)
    {
        $url = Config::get('app.web_ticket_photo_path');
        $url = str_replace('{id}', $ticket_id, $url);
        $url = str_replace('{guid}', $photo_guid, $url);

        if ($photo_guid == null) {
            $url = Config::get('app.web_url') . '/images/no-image.png';
        }

        return $url;
    }
}
