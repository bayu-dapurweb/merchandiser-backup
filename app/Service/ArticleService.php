<?php 
namespace App\Service;

class ArticleService
{
    public static function index()
    {
        $search = get('search');
        $tag = get('tag');
        $type = get('type');
        $is_curator_pick = get('is_curator_pick');

        $posts = \App\TrxPosts::with("thumb_media" , "main_media")
        ->where("post_type", "article");
        if (!empty($search)) {
            $search = alphanumeric_only($search);
            $posts = $posts->whereRaw("title like '%$search%'");
        }
        if (!empty($tag)) {
            $tag = alphanumeric_only($tag);
            $posts = $posts->whereRaw("json_meta like '%tag%' and json_meta like '%$tag%'");
        }
        if (!empty($is_curator_pick)) {
            $posts = $posts->whereRaw("json_meta like '%curator_pick\":\"1%'");
        }
        if (!empty($show_on_banner)) {
            $posts = $posts->whereRaw("json_meta like '%show_on_banner\":\"1%'");
        }
        if (!empty($type)) {
            if ($type == "latest") {
                $posts = $posts->orderBy("id", "desc");
            }
            if ($type == "currator-pick") {
                $posts = $posts->whereRaw("json_meta like '%curator_pick%1%'");
                $posts = $posts->orderBy("id", "desc");
            }
            if ($type == "show-on-banner") {
                $posts = $posts->whereRaw("json_meta like '%show_on_banner%1%'");
                $posts = $posts->orderBy("id", "desc");
            }
        } else {
            $posts = $posts->orderBy("id", "desc");
        }
        $posts = $posts->whereRaw("is_active = 1");
        $posts = $posts->paginate(get("limit", 10));

        $data = paginationExplode($posts);

        foreach ($data['data'] as $k => $v) {
            $data['data'][$k]['json_meta'] = json_decode($data['data'][$k]['json_meta'], true);
            if (!empty($data['data'][$k]['json_meta']['tag'])) {
                $data['data'][$k]['tags'] = \App\RefTags::whereIn("id", $data['data'][$k]['json_meta']['tag'])->get();
                $data['data'][$k]['viewers'] = (int)ArticleService::articleviwers($v['id']);
            }
        }

        return $data;
    }

    public static function tags()
    {
        return \App\RefTags::where("tag_type", "article")->get();
    }

    public static function find($id)
    {
        $posts = \App\TrxPosts::with("thumb_media" , "main_media")
        ->where("post_type", "article")
        ->where("is_active", "1")
        ->where("id", $id)
        ->first();

        if (empty($posts)) {
            return null;
        }

        $posts->json_meta = json_decode($posts->json_meta);
        if (!empty($posts->json_meta->tag)) {
            $posts->tags = \App\RefTags::whereIn("id", $posts->json_meta->tag)->get();
        }
        $posts->viewers = (int)ArticleService::articleviwers($posts->id);

        return $posts;
    }

    public static function articleviwers($id)
    {
        $viewers = \App\TrxArticleViews::selectRaw("count(*) as jumlah")->where("trx_posts_id", $id)->first();
        if (!empty($viewers) && $viewers->jumlah > 0) {
            return $viewers->jumlah;
        } else {
            return 0;
        }
    }
}