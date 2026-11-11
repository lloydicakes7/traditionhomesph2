<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Queries\Public\ContentQuery;

class ContentController extends Controller
{
    public function show(string $slug, ContentQuery $query)
    {
        $content = $query->page($slug);

        abort_if($content === null, 404);

        return view('public.content', [
            'content' => $content,
        ]);
    }
}
