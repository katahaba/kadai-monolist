<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

 use \App\Item;

  class ItemsController extends Controller
  {

    public function create()
    {
        $keyword = request()->keyword;//キーワードとはリクエストから送られてきたキーワードですよ。
        $items = [];//検索結果一覧を入れるためのitemsという配列を作っておき[]で初期化します。
        if ($keyword) {
            // 検索の準備としてクライアントインスタンスを作りAPPidを登録します。
            $client = new \RakutenRws_Client();
            $client->setApplicationId(env('RAKUTEN_APPLICATION_ID'));
            
            //オプションをつけて検索を実行します。
            // 検索ワードを設定し、画像があるもののみに絞り込み、20件検索するオプションです。この検索結果は $rws_response に代入されます。
            $rws_response = $client->execute('IchibaItemSearch', [
                'keyword' => $keyword,
                'imageFlag' => 1,
                'hits' => 20,
            ]);

            // 扱い易いように Item としてインスタンスを作成する（保存はしない）
            foreach ($rws_response->getData()['Items'] as $rws_item) {
                $item = new Item();
                $item->code = $rws_item['Item']['itemCode'];
                $item->name = $rws_item['Item']['itemName'];
                $item->url = $rws_item['Item']['itemUrl'];
                $item->image_url = str_replace('?_ex=128x128', '', $rws_item['Item']['mediumImageUrls'][0]['imageUrl']);
                // 予め初期化していたカラの配列である $items に$itemを追加していきます。注意したいのは、上書きではなく、追加している点です。
                $items[] = $item;
            }
        }

        return view('items.create', [
            'keyword' => $keyword,
            'items' => $items,
        ]);
    }
    
    
     public function show($id)
    {
        $item = Item::find($id);
        $want_users = $item->want_users;
        $have_users = $item->have_users;

        
        return view('items.show', [
          'item' => $item,
          'want_users' => $want_users,
          'have_users' => $have_users,
        ]);
    }
}