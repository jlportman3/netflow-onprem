<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NetflowDataController extends Controller
{
    /**
     * The Netflow collector swaps storage files every 5 minutes (default) and is setup to notify us via the API call
     * so that we can process the data.  Submit a job to handle the processing of data.
     */
    public function receiveFile(Request $request)
    {
        $data = json_decode($request->getContent());
        ray($data);
    }
}
