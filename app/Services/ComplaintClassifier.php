<?php

namespace App\Services;

use Niiknow\Bayes;

class ComplaintClassifier
{
    protected $bayes;

    public function __construct()
    {
        $this->bayes = new Bayes();
        $this->trainModel();
    }

    /**
     * Training model dari CSV
     */
    private function trainModel()
    {
        // Path ke file training data
        // $csvPath = storage_path('app/training_data_kategori.csv');

        $csvPath = resource_path('data/training_data_kategori.csv');

        // Cek file ada atau tidak
        if (!file_exists($csvPath)) {
            throw new \Exception("File training data tidak ditemukan di: {$csvPath}");
        }

        // Baca file CSV
        $file = fopen($csvPath, 'r');

        // Skip header (baris pertama)
        fgetcsv($file);

        // Baca setiap baris dan training
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 2 && !empty($row[0])) {
                $text = strtolower(trim($row[0]));      // Kolom text
                $category = strtolower(trim($row[1]));   // Kolom category

                // Training
                $this->bayes->learn($text, $category);
            }
        }

        fclose($file);
    }

    /**
     * Prediksi kategori dari text
     */
    public function predict($text)
    {
        // Bersihkan text dulu
        $cleanText = strtolower(trim($text));

        // Prediksi
        $category = $this->bayes->categorize($cleanText);

        return $category;
    }
}
