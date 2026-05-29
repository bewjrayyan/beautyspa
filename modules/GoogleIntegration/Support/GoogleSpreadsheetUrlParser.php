<?php

namespace Modules\GoogleIntegration\Support;

class GoogleSpreadsheetUrlParser
{
    /**
     * @return array{spreadsheet_id: string, sheet_gid: ?string}
     */
    public static function parse(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            return ['spreadsheet_id' => '', 'sheet_gid' => null];
        }

        $spreadsheetId = self::extractSpreadsheetId($input);
        $sheetGid = self::extractSheetGid($input);

        if ($spreadsheetId !== null) {
            return [
                'spreadsheet_id' => $spreadsheetId,
                'sheet_gid' => $sheetGid,
            ];
        }

        return [
            'spreadsheet_id' => $input,
            'sheet_gid' => null,
        ];
    }


    private static function extractSpreadsheetId(string $input): ?string
    {
        $marker = '/spreadsheets/d/';

        if (! str_contains($input, $marker)) {
            return null;
        }

        $start = strpos($input, $marker) + strlen($marker);
        $end = strcspn($input, '/?#', $start);

        $id = substr($input, $start, $end);

        return $id !== '' ? $id : null;
    }


    private static function extractSheetGid(string $input): ?string
    {
        foreach (['?gid=', '&gid=', '#gid='] as $needle) {
            $pos = strpos($input, $needle);

            if ($pos === false) {
                continue;
            }

            $start = $pos + strlen($needle);
            $gid = '';

            for ($i = $start, $len = strlen($input); $i < $len; $i++) {
                if (! ctype_digit($input[$i])) {
                    break;
                }

                $gid .= $input[$i];
            }

            if ($gid !== '') {
                return $gid;
            }
        }

        return null;
    }


    public static function toUrl(string $spreadsheetId, ?string $sheetGid = null): string
    {
        $url = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/edit';

        if ($sheetGid) {
            $url .= '?gid=' . $sheetGid;
        }

        return $url;
    }
}
