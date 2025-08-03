<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Laravel\Scout\Builder;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $posts = Post::search($request->input('q', '*'))
            ->when(is_numeric($request->input('created')), function (Builder $query) use ($request) {
                $query->where('created_at', ['>=', now()->subDays($request->integer('created'))->timestamp]);
            })
            ->when(! empty($request->input('exclude')), function (Builder $query) use ($request) {
                $query->whereNotIn('id', explode(',', $request->input('exclude')));
            })
            ->when($request->input('not_title'), function (Builder $query, string $title) {
                $query->where('title', ['!', $title]);
            })
            ->get();

        return $posts->load('comments');
    }
}
