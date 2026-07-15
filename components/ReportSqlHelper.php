<?php

namespace components;

class ReportSqlHelper
{
    public static function buildHospcodeInList($hospcode)
    {
        if ($hospcode === null || $hospcode === '' || $hospcode === 'all') {
            return '';
        }

        $quoted = [];
        foreach (preg_split('/\s*,\s*/', (string)$hospcode, -1, PREG_SPLIT_NO_EMPTY) as $code) {
            $code = trim($code, " \t\n\r\0\x0B'\"");
            if ($code === '' || !preg_match('/^[0-9A-Za-z_-]+$/', $code)) {
                continue;
            }
            $quoted[$code] = self::quoteValue($code);
        }

        return implode(',', $quoted);
    }

    public static function applyHospcodeFilter($sql, $hospcode)
    {
        $hospcodeList = self::buildHospcodeInList($hospcode);
        $sql = rtrim(trim((string)$sql), " \t\n\r;");
        if ($hospcodeList === '' || !preg_match('/\bHOSPCODE\b/i', $sql)) {
            return $sql;
        }

        $prefix = '';
        $selectSql = $sql;
        $lastStatementBreak = strrpos($sql, ';');
        if ($lastStatementBreak !== false) {
            $prefix = rtrim(substr($sql, 0, $lastStatementBreak + 1)) . "\r\n";
            $selectSql = trim(substr($sql, $lastStatementBreak + 1));
        }

        $hospcodeExpression = self::resolveHospcodeExpression($selectSql);
        if ($hospcodeExpression === null) {
            return $sql;
        }

        $tailOffset = self::findTopLevelClause($selectSql, ['group by', 'having', 'order by', 'limit']);
        $head = $tailOffset === false ? $selectSql : rtrim(substr($selectSql, 0, $tailOffset));
        $tail = $tailOffset === false ? '' : ' ' . ltrim(substr($selectSql, $tailOffset));
        $filter = $hospcodeExpression . " IN ($hospcodeList)";
        $whereOffset = self::findTopLevelClause($head, ['where']);

        if ($whereOffset === false) {
            $head .= " WHERE $filter";
        } else {
            $head .= " AND $filter";
        }

        return $prefix . $head . $tail;
    }

    public static function normalizeProcedureBody($sql)
    {
        $sql = rtrim((string)$sql);
        return $sql !== '' && substr($sql, -1) !== ';' ? $sql . ';' : $sql;
    }

    public static function safeIdentifierSuffix($value, $label = 'identifier')
    {
        $value = (string)$value;
        if (!preg_match('/^[A-Za-z0-9_]+$/', $value)) {
            if (class_exists('\yii\web\BadRequestHttpException')) {
                throw new \yii\web\BadRequestHttpException('ข้อมูลที่ระบุไม่ถูกต้อง');
            }
            throw new \InvalidArgumentException('ข้อมูลที่ระบุไม่ถูกต้อง');
        }

        return $value;
    }

    public static function classifySql($sql)
    {
        $sql = trim((string)$sql);
        $lastStatement = $sql;
        $lastStatementBreak = strrpos(rtrim($sql, " \t\n\r;"), ';');
        if ($lastStatementBreak !== false) {
            $lastStatement = trim(substr($sql, $lastStatementBreak + 1));
        }

        return [
            'has_hospcode' => (bool)preg_match('/\bHOSPCODE\b/i', $sql),
            'has_top_level_where' => self::findTopLevelClause($lastStatement, ['where']) !== false,
            'has_top_level_order_by' => self::findTopLevelClause($lastStatement, ['order by']) !== false,
            'has_top_level_group_by' => self::findTopLevelClause($lastStatement, ['group by']) !== false,
            'has_top_level_having' => self::findTopLevelClause($lastStatement, ['having']) !== false,
            'has_top_level_limit' => self::findTopLevelClause($lastStatement, ['limit']) !== false,
            'has_multiple_statements' => $lastStatementBreak !== false,
            'has_select_star' => (bool)preg_match('/\bselect\s+\*/i', $lastStatement),
            'hospcode_expression' => self::resolveHospcodeExpression($lastStatement),
        ];
    }

    public static function findTopLevelClause($sql, array $keywords, $startAt = 0)
    {
        $depth = 0;
        $quote = null;
        $length = strlen((string)$sql);

        for ($i = $startAt; $i < $length; $i++) {
            $char = $sql[$i];
            if ($quote !== null) {
                if ($char === $quote && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $quote = null;
                }
                continue;
            }

            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                continue;
            }
            if ($char === '(') {
                $depth++;
                continue;
            }
            if ($char === ')') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($depth !== 0 || !preg_match('/\s|^/', $i === 0 ? '' : $sql[$i - 1])) {
                continue;
            }

            $remaining = substr($sql, $i);
            foreach ($keywords as $keyword) {
                if (preg_match('/^' . str_replace(' ', '\s+', preg_quote($keyword, '/')) . '\b/i', $remaining)) {
                    return $i;
                }
            }
        }

        return false;
    }

    public static function resolveHospcodeExpression($selectSql)
    {
        $selectSql = trim((string)$selectSql);
        $fromOffset = self::findTopLevelClause($selectSql, ['from']);

        if ($fromOffset !== false) {
            $fromSql = ltrim(substr($selectSql, $fromOffset + 4));
            if (isset($fromSql[0]) && $fromSql[0] === '(') {
                $alias = self::resolveDerivedTableAlias($fromSql);
                if ($alias !== null) {
                    return $alias . '.HOSPCODE';
                }
            }
        }

        if (preg_match('/\)\s*(?:as\s+)?(`?[A-Za-z0-9_]+`?)\s+where\s+1\s*=\s*1\s*$/i', $selectSql, $match)) {
            return $match[1] . '.HOSPCODE';
        }
        if (preg_match('/(?:select|,)\s*((?:`[^`]+`|[A-Za-z0-9_]+)(?:\s*\.\s*(?:`[^`]+`|[A-Za-z0-9_]+))?)\s+(?:as\s+)?`?HOSPCODE`?\b/i', $selectSql, $match)) {
            return preg_replace('/\s+/', '', $match[1]);
        }
        if ($fromOffset !== false && preg_match('/^\s*((?:`[^`]+`|[A-Za-z0-9_]+)(?:\s*\.\s*(?:`[^`]+`|[A-Za-z0-9_]+))?)(?:\s+(?:as\s+)?(`?[A-Za-z0-9_]+`?))?/i', substr($selectSql, $fromOffset + 4), $match)) {
            $relation = isset($match[2]) && !preg_match('/^(inner|left|right|join|where|group|order|having|limit)$/i', trim($match[2], '`'))
                ? $match[2]
                : $match[1];
            return preg_replace('/\s+/', '', $relation) . '.HOSPCODE';
        }
        if (preg_match('/((?:`[^`]+`|[A-Za-z0-9_]+)\s*\.\s*`?HOSPCODE`?)/i', $selectSql, $match)) {
            return preg_replace('/\s+/', '', $match[1]);
        }
        if (preg_match('/\b`?HOSPCODE`?\b/i', $selectSql)) {
            return 'HOSPCODE';
        }

        return null;
    }

    private static function resolveDerivedTableAlias($fromSql)
    {
        $depth = 0;
        $quote = null;
        $length = strlen($fromSql);
        for ($i = 0; $i < $length; $i++) {
            $char = $fromSql[$i];
            if ($quote !== null) {
                if ($char === $quote && ($i === 0 || $fromSql[$i - 1] !== '\\')) {
                    $quote = null;
                }
                continue;
            }
            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                continue;
            }
            if ($char === '(') {
                $depth++;
                continue;
            }
            if ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    $afterDerivedTable = ltrim(substr($fromSql, $i + 1));
                    if (preg_match('/^(?:as\s+)?(`?[A-Za-z0-9_]+`?)\b/i', $afterDerivedTable, $match)) {
                        return $match[1];
                    }
                    break;
                }
            }
        }

        return null;
    }

    private static function quoteValue($value)
    {
        if (class_exists('\Yii') && isset(\Yii::$app) && isset(\Yii::$app->db)) {
            return \Yii::$app->db->quoteValue($value);
        }

        return "'" . str_replace("'", "''", (string)$value) . "'";
    }
}
