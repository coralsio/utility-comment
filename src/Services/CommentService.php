<?php

namespace Corals\Utility\Comment\Services;

use Corals\Foundation\Services\BaseServiceClass;
use Corals\Utility\Comment\Classes\CommentManager;

class CommentService extends BaseServiceClass
{
    /**
     * @param $data
     * @param $commentableClass
     * @param $commentable_hashed_id
     * @param null $author
     * @return \Corals\Utility\Comment\Models\Comment|\Illuminate\Database\Eloquent\Model
     */
    public function createComment($data, $commentableClass, $commentable_hashed_id, $author = null)
    {
        if (is_null($commentableClass)) {
            abort(400, 'Comment class is null');
        }

        $commentable = $commentableClass::findByHash($commentable_hashed_id);

        if (! $commentable) {
            abort(404, 'Not Found!!');
        }

        if (is_null($author) && user()) {
            $author = user();
        }

        $commentableManagerClass = new CommentManager($commentable, $author);

        return $commentableManagerClass->createComment([
            'body' => $data['body'],
            'status' => $data['status'] ?? null,
            'is_private' => in_array(data_get($data, 'is_private'), ['true', 1]),
            'properties' => $data['properties'] ?? [],
        ]);
    }

    public function replyComment($data, $comment)
    {
        $user = user() ?? null;

        $commentableClass = new CommentManager($comment, $user);

        $reply = $commentableClass->createComment([
            'body' => $data['body'],
            'properties' => $data['properties'],
            'status' => $data['status'] ?? null,
        ]);

        return $reply;
    }
}
