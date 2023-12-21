<?php

namespace App\Http\Controllers;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $source = $request->get('source');
      $ip_impr = $request->get('ip_impr');
      $port_impr = $request->get('port_impr');
      $connector = new NetworkPrintConnector($ip_impr, $port_impr);
      $printer = new Printer($connector);
      try {
        $response = Http::get($source);
        Storage::put('print.html', $response->body());
        $html  = storage_path('app/print.html');
        $output = storage_path('app/image.png');
        exec("wkhtmltoimage --format png ${html} ${output}");
        $dest=storage_path('app/image.png');
    try {
        $img = EscposImage::load($dest);
    } catch (Exception $e) {
        throw $e;
    }
    $printer -> bitImage($img);
    
    $printer -> cut();
} catch (Exception $e) {
    echo $e -> getMessage();
} finally {
    $printer -> close();
}
    }

   
}
