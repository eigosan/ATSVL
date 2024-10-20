<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;


class ResumeController extends Controller
{
    public function upload(Request $request)
    {
        // Validate file upload
        $validatedData = $request->validate([
            'resume' => 'required|file|mimes:pdf,docx,doc',
        ]);

        // Store file in storage
        $filePath = Storage::putFile('resumes', $validatedData['resume']);

        // Parse resume file
        $parsedResume = $this->parseResume(storage_path('app/' . $filePath));

        // Store parsed resume data in database
        // ...

        return response()->json(['message' => 'Resume uploaded successfully', 'data' => $parsedResume]);
    }

    private function parseResume($filePath)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($fileExtension === 'pdf') {
            return $this->parsePdf($filePath);
        } elseif (in_array($fileExtension, ['docx', 'doc'])) {
            return $this->parseDocx($filePath);
        } else {
            throw new \Exception("Unsupported file type: {$fileExtension}");
        }
    }

    private function parsePdf($filePath)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        return $this->extractInformation($text);
    }

    private function parseDocx($filePath)
{
    $phpWord = WordIOFactory::load($filePath);
    $text = '';

    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if ($element instanceof Text) {
                $text .= $element->getText() . "\n";
            } elseif ($element instanceof TextRun) {
                foreach ($element->getElements() as $textRunElement) {
                    if ($textRunElement instanceof Text) {
                        $text .= $textRunElement->getText();
                    }
                }
                $text .= "\n";
            }
        }
    }

    return $this->extractInformation($text);
}
    private function extractInformation($text)
    {
        return [
            'name' => $this->extractName($text),
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text),
            'skills' => $this->extractSkills($text),
        ];
    }

    private function extractName($text)
    {
        // Simple regex to find names (this can be improved)
        $namePattern = '/([A-Z][a-z]+(?:\s[A-Z][a-z]+)*)/';
        preg_match($namePattern, $text, $matches);
        return $matches[0] ?? null;
    }

    private function extractEmail($text)
    {
        $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        preg_match($emailPattern, $text, $matches);
        return $matches[0] ?? null;
    }

    private function extractPhone($text)
    {
        $phonePattern = '/\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/';
        preg_match($phonePattern, $text, $matches);
        return $matches[0] ?? null;
    }

    private function extractSkills($text)
    {
        // Example skills list (this can be expanded)
        $skillsList = ['Python', 'Java', 'JavaScript', 'SQL', 'Machine Learning'];
        $foundSkills = array_filter($skillsList, function($skill) use ($text) {
            return stripos($text, $skill) !== false;
        });
        return array_values($foundSkills);
    }
}
