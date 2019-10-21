<?php

namespace App\Helpers;

use App\Models\TicketComments;
use Validator;
use Illuminate\Support\Facades\File;
use Response;
use Illuminate\Support\Str;


class FilesHelper
{
    public static function handleAttachedFiles($attachments, $hash, $commentId = null, $noteId = null)
    {

        $ticketFilesData = [];
        foreach ($attachments as $attachment) {
            $docEx = config('filesystems.doc_mimes');
            $extension = explode('/', $attachment['type']);

            $docType = 'images';

            if (in_array($extension[1], $docEx)) {
                $docType = 'documents';
            }

            array_push($ticketFilesData, [
                'ticket_id' => $hash,
                'file_name' => $attachment['name'],
                'file_full_path' => $attachment['link'],
                'file_type' => $attachment['type'],
                'disposition' => $attachment['disposition'],
                'main_type' => $docType,
                'comment_id' => $commentId,
                'note_id' => $noteId,
            ]);
        }
        Helper::insertTo('ticket_files', $ticketFilesData);

        return $ticketFilesData;
    }

    public static function uploadFile($file)
    {
        $fileType = explode('/', $file->getMimeType());

        $destinationPath = public_path() . '/uploads/' . Helper::$subDomain . '/ticket/' . $fileType[1];

        $fileName = time() . '_' . $file->getClientOriginalName();

        $file->move($destinationPath, $fileName);

        $link = 'https://' . env('APP_PROD') . env('PAGE_URL') . '/uploads/' . Helper::$subDomain . '/ticket/' . $fileType[1] . '/' . $fileName;

        return $link;
    }

    /**
     * creating folder if not exist by company name and ticket id
     * moving file from email to our system folder
     * @param $company_name
     * @param int $ticket_id
     * @param int $commentId
     * @param int $main_ticket_id
     * @param $attachments
     * @return array of attached files
     */
    public static function uploadFileFromEmail($company_name, $ticket_id, $attachments, $commentId = null)
    {
//        $emailBody = TicketComments::where('id', $commentId)->first()['body'];
        $emailTicketType = "comment";

        //set destination path for file
        $destination = public_path('uploads' . DIRECTORY_SEPARATOR . $company_name . DIRECTORY_SEPARATOR . 'ticket_' . $ticket_id);
        //if folder is not exists than creates it
        if (!File::exists($destination)) {
            //removing default mask
            umask(0);
            //creating with 0777 permission for be possible to delete in the future
            mkdir($destination, 0777, true);
        }

        $attachedFiles = [];
        foreach ($attachments as $attachment) {
            //set random file name
            $fileName = Str::random(15) . '_' . $attachment->getFilename();
            //get file extension
            $extension = File::extension($attachment->getFilename());

            //validate files to know it is image or other document
            $imageEx = config('filesystems.image_mimes');
            $docEx = config('filesystems.doc_mimes');

            if (in_array($extension, $imageEx) && File::size($destination) < 20000) {
                $docType = 'images';
                File::put($destination . '/' . $fileName, $attachment->getContent(), 0644);
            } elseif (in_array($extension, $docEx) && File::size($destination) < 20000) {
                $docType = 'documents';
                File::put($destination . '/' . $fileName, $attachment->getContent(), 0644);
            } else {
                $docType = 'not valid';
            }

//            if ($docType == 'images' && !empty($emailBody)) {
//                $emailBody = str_replace(
//                    "cid:" . $attachment->getContentId(),
//                    "https://" . env("APP_PROD") . env("PAGE_URL") . '/uploads/' . $company_name . '/ticket_' . $ticket_id . '/' . $fileName,
//                    $emailBody
//                );
//            }

            $attachedFiles[] = [
                'extension' => $extension,
                'ticket_id' => $ticket_id,
                'full_path' => "https://" . env("APP_PROD") . env("PAGE_URL") . '/uploads/' . $company_name . '/ticket_' . $ticket_id . '/' . $fileName,
                'fileName' => $fileName,
                'cid' => $attachment->getContentId(),
                'disposition' => $attachment->getContentDisposition(),
                'fileSize' => File::size($destination),
                'fileType' => File::mimeType($destination),
                'orig_name' => $attachment->getFilename(),
                'comment_id' => isset($commentId) ? $commentId : null,
                'type' => $docType
            ];
        }

//        TicketComments::where('id', $commentId)->update(["body" => $emailBody]);

        return $attachedFiles;
    }

}