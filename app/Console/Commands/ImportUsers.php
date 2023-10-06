<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a CSV file';

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
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'required|string|max:255',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users', 'email'),
                    ],
                    'phoneNumber' => [
                        'required',
                        'regex:/^(1-\d{3}-\d{3}-\d{4}|\(\d{3}\) \d{3}-\d{4}|\+\d{1}-\d{3}-\d{3}-\d{4}|\d{11})$/'
                    ],
                ]);
                
                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }
                $password = "Test@1234";
                $data['password'] = bcrypt($password);

                User::updateOrCreate(['email' => $data['email']], $data);

                $successCount++;
            } catch (ValidationException $e) {
                // Log validation failures with reasons
                Log::error('User import failed: ' . $e->getMessage(), ['data' => $data]);
                $failCount++;
            }
        }

        Log::info("Users imported: {$successCount}, Users failed: {$failCount}");
    }
}
