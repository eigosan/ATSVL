namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controllers{
    public function upload(Request $request){
        // Validate file upload
        $validatedData = $request->validate([
            'resume' => 'required|file|mimes:pdf,docx,doc'
            ]);
        // Store file in Storage
        $filePath = Storage::putFile('resumes', $validatedData['resume']);

        // Parse resume file
        $parsedResume = $this->parseResume($filePath);
        // Store parsed resume data in database
        // ..

        return response()->json(['message' => 'Resume uploaded succesfully']);

    }
    private function parseResume($filePath){


    }

}
