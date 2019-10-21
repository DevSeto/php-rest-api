<?php

namespace App\Services;


use App\Models\Notes;
use Sunra\PhpSimple\HtmlDomParser;

class NoteService
{
    public function getNotesByTicketId($ticketId)
    {
        $notes = Notes::where('ticket_id', $ticketId)->with(['author', 'files'])->get();
        if (!empty($notes)) {
            return $notes->toArray();
        }

        return $notes;
    }

    public function getNoteByTicketIdAndNoteId($ticketId, $noteId)
    {
        return Notes::where('ticket_id', $ticketId)->where('id', $noteId)->with('author')->first();
    }

    /**
     * Get mentioned user IDs in a note
     * @param string $htmlString
     * @return array
     */
    public function getMentionedUsersIDs($htmlString)
    {
        $dom = HtmlDomParser::str_get_html($htmlString);
        $dataUsersIDs = [];
        $tags = $dom->find('span[data-id]');
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $dataUsersIDs[] = $tag->attr['data-id'];
            }
            $dataUsersIDs = array_unique($dataUsersIDs);
        }
        return $dataUsersIDs;
    }
}