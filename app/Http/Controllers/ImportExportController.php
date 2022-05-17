<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\BulkExport;
use App\Imports\BulkImport;
use App\Models\Bulk;
// use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Excel;
use QrCode;
use Image;
use File;
use ZipArchive;
class ImportExportController extends Controller
{
    //

   

    public function importExportView()
    {
       return view('importexport');
    }


    public function import(Request $request) 
    {
        //for adding all the data of excel in database
        Excel::import(new BulkImport,$request->file('file'));

        //getting the inserted data from database
        $table_columns = Bulk::all();
        try{
            foreach($table_columns as $column)
            {   

                //adding qrcode to the image
                $image = QrCode::format('png')->size(250)->generate($column->Qr_data);
                $output_file = '/qrcode/img-'.$column->PIN.'.png';
                $url  = Storage::disk('public')->put($output_file, $image);
                $img = Image::make('Template.png');
                $output_image = 'img-'.$column->PIN.'.png';
                $img->insert('storage/qrcode/'.$output_image,'center', 255);
                $img->save($column->PIN.'.jpg');
                

                //adding pin and Id to the image

                //for PIN
                 $img->text($column->PIN, 140, 275, function($font) {
                    $font->file(public_path('Roboto-Bold.ttf'));  
                    $font->size(45);   
                    $font->color('#e1e1e1');  
                    $font->align('top');  
                    $font->valign('bottom'); 
                });  
                $img->save($column->PIN.'.jpg');  

                //For Id
                $img->text($column->IDE, 110, 210, function($font) {
                    $font->file(public_path('Roboto-Bold.ttf'));  
                    $font->size(45);  
                    $font->color('#e1e1e1');  
                    $font->align('top');  
                    $font->valign('bottom');  
                });  

                //Saving Image
                $img->save($column->PIN.'.jpg');  

                //copying all the files in data folder which are in public folder
                if (! File::exists(public_path().'/data')) {
                    File::makeDirectory(public_path().'/data');
                }
               
                File::move(public_path($column->PIN.'.jpg'), public_path('data/'.$column->PIN.'.jpg'));
                File::deleteDirectory(public_path($column->PIN.'.jpg'));
                

            }
            //deleting qrcode folder from storage
            File::deleteDirectory(public_path('storage/qrcode'));

            //creating zip File
            $zip = new ZipArchive;
            $fileName = 'myNewFile.zip';
            if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
            {
                $files = File::files(public_path('data'));
                foreach ($files as $key => $value) {
                    $relativeNameInZipFile = basename($value);
                    $zip->addFile($value, $relativeNameInZipFile);
                }
                $zip->close();
            }

            //deleting datafrom mysql
            Bulk::where('id', 'like' ,'%%')->delete();

            //deleting extra files from server
            File::deleteDirectory(public_path('data'));

            //downloading the zip file made
            return response()->download(public_path($fileName));
        }catch(exception $e){
            return $e;
        }
       
        return "Work Done";
    }
}
