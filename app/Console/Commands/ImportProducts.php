<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Product;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData); // Remove the header row
        $successCount = 0;
        $failCount = 0;

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            try {
                // Validate and process the data here
                $validator = Validator::make($data, [
                    'ID' => 'required|string|max:255',
                    'productname' => 'required|string|max:255',
                    'price' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                Product::create([
                    "productname" => $data['productname'],
                    "price" => $data['price'],
                ]);

                $successCount++;
            } catch (ValidationException $e) {
                // Log validation failures with reasons
                Log::error('product import failed: ' . $e->getMessage(), ['data' => $data]);
                $failCount++;
            }
        }

        Log::info("products imported: {$successCount}, products failed: {$failCount}");
    }
}
