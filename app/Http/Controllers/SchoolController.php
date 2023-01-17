<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Assert;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpParser\Node\Stmt\For_;

use function PHPUnit\Framework\isTrue;

class SchoolController extends Controller
{
    public function uplode(Request $request){

        IF  (!$request->name_district){
            $nameFile = $this->getNameFile(explode(" ", $request->file('file')->getClientOriginalName()));
            if  (!$nameFile) return response()->json(['message' => 'Failed find name district'], 400);

            // dd($nameFile);

            if (!$district = District::where('name', $nameFile)->first())
                return response()->json(['message' => 'Failed find district id'], 400);
        } else {
            if (!$district = District::where('name', $request->name_district)->first())
                return response()->json(['message' => 'Failed find district id'], 400);
        }

        // $district = District::where('name', $request->name_district)->first();

        // if  (!$district->id) return response()->json(['message' => 'Failed find district'], 400);

        $name = $request->file('file')->store('file');
        $path = storage_path() . '/app/' . $name;

        $reader = new ReaderXlsx();
        $spreedsheet = $reader->load($path);
        $sheet = $spreedsheet->getActiveSheet();

        $row = 3;

        while ( true ){
            if($sheet->getCell("B{$row}")->getValue() && $sheet->getCell("B{$row}")->getValue() != "Total"
             && !Assert::assertNotNull($sheet->getCell("B{$row}")->getValue())){

                /* Validate unique table colume
                $isTrue = School::where('name', $sheet->getCell("B{$row}")->getValue())->first();
                // dd($isTrue);
                if ($isTrue != null)
                    return response()->json(['message' => 'Failed data is already have ' . $sheet->getCell("B{$row}")->getValue()], 400);
                */

                School::create([
                    'distric_id' => $district->id,
                    'name' => $sheet->getCell("B{$row}")->getValue()
                ]);

                // echo $sheet->getCell("B{$row}")->getValue() . PHP_EOL;
                $row++;
            } else
                break;
        }

        if (Storage::delete($name)) return response()->json(['message' => 'Done input & delete file temp'], 200);

        return response()->json(['message' => 'Failed'], 400);

    }

    public function getNameFile($names):String {

        $name = "";
        $kec = 0;

        for ($i=0; $i < count($names); $i++) {
            if  ($names[$i] == "Kec.") {
                $kec = $i;
                break;
            }
        }

        for ($i=$kec+1; $i < count($names); $i++) {
            // dd($names);
            if ($name == ""){
                $name = $names[$i];
            } else $name =$name .  " " . $names[$i];

            if ($names[$i + 1] == "-")
                return $name;
        }

        return null;
    }
}
