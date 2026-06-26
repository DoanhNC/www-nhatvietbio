<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\ELanguage;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    /**
     * Get all languages
     */
    public function index(Request $request)
    {
        $query = ELanguage::orderBy('position');

        if ($request->has('active_only') && $request->active_only === 'true') {
            $query->where('is_active', 1);
        }

        $languages = $query->get()->map(function ($lang) {
            return [
                'id' => $lang->id,
                'code' => $lang->code,
                'name' => $lang->name,
                'flag' => $lang->flag,
                'flag_icon' => $lang->flag_icon,
                'is_default' => $lang->is_default,
                'is_active' => $lang->is_active,
                'position' => $lang->position,
                'translation_count' => count($lang->getFlatTranslations()),
                'translations_hash' => $lang->translations_hash ?? $lang->generateHash(),
                'created_at' => $lang->created_at,
            ];
        });

        return response()->json([
            'languages' => $languages,
            'count' => $languages->count(),
        ]);
    }

    /**
     * Create a new language
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:e_languages,code',
            'name' => 'required|string|max:255',
            'flag' => 'nullable|string|max:50',
            'flag_icon' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'copy_from' => 'nullable|exists:e_languages,id',
        ], [
            'code.required' => 'Vui lòng nhập mã ngôn ngữ',
            'code.unique' => 'Mã ngôn ngữ đã tồn tại',
            'name.required' => 'Vui lòng nhập tên ngôn ngữ',
        ]);

        $maxPosition = ELanguage::max('position') ?? 0;

        // Copy translations from another language if specified
        $translations = [];
        if (!empty($data['copy_from'])) {
            $sourceLanguage = ELanguage::find($data['copy_from']);
            if ($sourceLanguage) {
                $translations = $sourceLanguage->translations ?? [];
            }
        }

        $language = ELanguage::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'flag' => $data['flag'] ?? null,
            'flag_icon' => $data['flag_icon'] ?? null,
            'is_default' => 0,
            'is_active' => $data['is_active'] ?? 1,
            'position' => $maxPosition + 1,
            'translations' => $translations,
        ]);

        return ApiResponse::success($language, 201);
    }

    /**
     * Get a specific language with its translations
     */
    public function show($id)
    {
        $language = ELanguage::findOrFail($id);

        return response()->json([
            'id' => $language->id,
            'code' => $language->code,
            'name' => $language->name,
            'flag' => $language->flag,
            'is_default' => $language->is_default,
            'is_active' => $language->is_active,
            'position' => $language->position,
            'translations' => $language->translations,
            'flat_translations' => $language->getFlatTranslations(),
        ]);
    }

    /**
     * Update a language
     */
    public function update(Request $request, $id)
    {
        $language = ELanguage::findOrFail($id);

        $data = $request->validate([
            'code' => 'sometimes|string|max:10|unique:e_languages,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'flag' => 'nullable|string|max:50',
            'flag_icon' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'translations' => 'nullable|array',
        ]);

        // Don't allow deactivating default language
        if ($language->is_default && isset($data['is_active']) && !$data['is_active']) {
            return ApiResponse::error('Không thể vô hiệu hóa ngôn ngữ mặc định', 422);
        }

        // Update and refresh to get latest data
        $language->update($data);
        $language->refresh();

        return response()->json([
            'status' => true,
            'data' => $language,
            'debug' => [
                'received_translations' => isset($data['translations']),
                'translations_count' => isset($data['translations']) ? count($data['translations']) : 0,
                'saved_translations_count' => is_array($language->translations) ? count($language->translations) : 0,
            ]
        ]);
    }

    /**
     * Delete a language
     */
    public function destroy($id)
    {
        $language = ELanguage::findOrFail($id);

        if ($language->is_default) {
            return ApiResponse::error('Không thể xóa ngôn ngữ mặc định', 422);
        }

        $language->delete();

        return ApiResponse::success('Xóa ngôn ngữ thành công');
    }

    /**
     * Set language as default
     */
    public function setDefault($id)
    {
        $language = ELanguage::findOrFail($id);
        $language->setAsDefault();

        return ApiResponse::success('Đã đặt làm ngôn ngữ mặc định');
    }

    /**
     * Update positions
     */
    public function updatePositions(Request $request)
    {
        $data = $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:e_languages,id',
            'positions.*.position' => 'required|integer|min:0',
        ]);

        foreach ($data['positions'] as $item) {
            ELanguage::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return ApiResponse::success('Cập nhật thứ tự thành công');
    }

    /**
     * Get all translations for frontend (public endpoint)
     * Returns all active languages with their translations and hash for caching
     */
    public function getTranslations()
    {
        $languages = ELanguage::where('is_active', 1)
            ->orderBy('position')
            ->get();

        $default = $languages->firstWhere('is_default', 1);
        $defaultCode = $default ? $default->code : ($languages->first()?->code ?? 'vi');

        $translations = [];
        $hashes = [];
        $supportedLangs = [];
        $langMeta = [];

        foreach ($languages as $lang) {
            $translations[$lang->code] = $lang->translations ?? [];
            $hashes[$lang->code] = $lang->translations_hash ?? $lang->generateHash();
            $supportedLangs[] = $lang->code;
            $langMeta[$lang->code] = [
                'name' => $lang->name,
                'flag' => $lang->flag,
                'flag_icon' => $lang->flag_icon,
            ];
        }

        // Combined hash of all translations for quick cache validation
        $combinedHash = md5(implode('', $hashes));

        return response()->json([
            'translations' => $translations,
            'hashes' => $hashes,
            'combinedHash' => $combinedHash,
            'defaultLang' => $defaultCode,
            'supportedLangs' => $supportedLangs,
            'langMeta' => $langMeta,
        ]);
    }

    /**
     * Convert PHP array string to JSON
     */
    public function convertPhpToJson(Request $request)
    {
        $data = $request->validate([
            'php_content' => 'required|string',
        ]);

        $phpContent = $data['php_content'];

        try {
            // Normalize line endings
            $phpContent = str_replace(["\r\n", "\r"], "\n", $phpContent);

            // Remove <?php tag and surrounding whitespace
            $phpContent = preg_replace('/^\s*<\?php\s*/i', '', $phpContent);

            // Remove single-line comments but keep string contents intact
            // We need to be careful not to remove // inside strings
            $lines = explode("\n", $phpContent);
            $cleanedLines = [];
            foreach ($lines as $line) {
                // Simple approach: remove // comments only if they're not inside a string
                // This is a basic approach - check if // appears after the last quote
                $trimmedLine = trim($line);
                if (strpos($trimmedLine, '//') === 0) {
                    // Line starts with comment, skip it
                    continue;
                }
                // Find // that's not inside a string (basic check)
                $inString = false;
                $stringChar = null;
                $result = '';
                for ($i = 0; $i < strlen($line); $i++) {
                    $char = $line[$i];
                    if (!$inString && ($char === '"' || $char === "'")) {
                        $inString = true;
                        $stringChar = $char;
                        $result .= $char;
                    } elseif ($inString && $char === $stringChar && ($i === 0 || $line[$i - 1] !== '\\')) {
                        $inString = false;
                        $stringChar = null;
                        $result .= $char;
                    } elseif (!$inString && $char === '/' && $i + 1 < strlen($line) && $line[$i + 1] === '/') {
                        // Found comment outside string, stop here
                        break;
                    } else {
                        $result .= $char;
                    }
                }
                $cleanedLines[] = $result;
            }
            $phpContent = implode("\n", $cleanedLines);

            // Find the array content - look for return followed by [ or array(
            // Use a more robust approach: find 'return' and extract everything until the matching ]
            $arrayStr = null;

            // Try short array syntax first: return [...]
            if (preg_match('/return\s*(\[[\s\S]*\])\s*;?\s*$/m', $phpContent, $matches)) {
                $arrayStr = $matches[1];
            }
            // Try old array syntax: return array(...)
            elseif (preg_match('/return\s*(array\s*\([\s\S]*\))\s*;?\s*$/m', $phpContent, $matches)) {
                $arrayStr = $matches[1];
            }

            if (!$arrayStr) {
                // Fallback: try to find return position and extract from there
                $returnPos = strpos($phpContent, 'return');
                if ($returnPos !== false) {
                    $afterReturn = substr($phpContent, $returnPos + 6);
                    $afterReturn = trim($afterReturn);

                    // Find the opening bracket
                    if (strpos($afterReturn, '[') === 0 || strpos($afterReturn, 'array') === 0) {
                        // Remove trailing semicolon if present
                        $afterReturn = rtrim($afterReturn);
                        if (substr($afterReturn, -1) === ';') {
                            $afterReturn = substr($afterReturn, 0, -1);
                        }
                        $arrayStr = trim($afterReturn);
                    }
                }
            }

            if (!$arrayStr) {
                return ApiResponse::error('Không tìm thấy cấu trúc return [...] hoặc return array(...)', 400);
            }

            // Create a temporary PHP file to evaluate
            $tempCode = '<?php return ' . $arrayStr . ';';
            $tempFile = tempnam(sys_get_temp_dir(), 'php_lang_');
            file_put_contents($tempFile, $tempCode);

            // Evaluate the PHP array
            $result = include $tempFile;
            unlink($tempFile);

            if (!is_array($result)) {
                return ApiResponse::error('Kết quả không phải là mảng hợp lệ', 400);
            }

            return response()->json([
                'status' => true,
                'json' => $result,
                'message' => 'Convert thành công',
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error('Lỗi parse PHP: ' . $e->getMessage(), 400);
        }
    }
}
