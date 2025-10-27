<?php
header('Content-Type: text/html; charset=utf-8');

// --- Текст из формы ---
$raw = isset($_POST['data']) ? (string)$_POST['data'] : '';

// --- Выбор стратегии обработки: mb_* если доступны, иначе перекодировка через iconv в cp1251 ---
$use_mb = function_exists('mb_strlen') && function_exists('mb_strtolower');

// Функции-обёртки
function u_strlen($s) {
    global $use_mb;
    if ($use_mb) {
        return mb_strlen($s, 'UTF-8');
    } else {
        $cp = iconv('UTF-8', 'CP1251//IGNORE', $s);
        return strlen($cp);
    }
}
function u_strtolower($s) {
    global $use_mb;
    if ($use_mb) {
        return mb_strtolower($s, 'UTF-8');
    } else {
        $cp = iconv('UTF-8', 'CP1251//IGNORE', $s);
        $cp = strtolower($cp);
        return iconv('CP1251', 'UTF-8//IGNORE', $cp);
    }
}

// Безопасный вывод
function h($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ЛР 10 — Результаты анализа</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1>Результаты анализа текста</h1>
    </div>
  </header>
  <main class="site-main container">
    <?php if (trim($raw) === ''): ?>
      <section class="card">
        <div class="src_error">Нет текста для анализа</div>
      </section>
    <?php else: ?>
      <section class="card">
        <h2>Исходный текст</h2>
        <div class="src_text"><em><?= nl2br(h($raw)) ?></em></div>
      </section>

      <?php
        $text = $raw; 
        // 1. количество символов в тексте (включая пробелы)
        $char_count = u_strlen($text);

        // 2. количество букв
        if (preg_match_all('/\p{L}/u', $text, $m)) { $letters_all = count($m[0]); } else { $letters_all = 0; }

        // 3. количество строчных и заглавных букв по отдельности
        if (preg_match_all('/\p{Ll}/u', $text, $m)) { $letters_lower = count($m[0]); } else { $letters_lower = 0; }
        if (preg_match_all('/\p{Lu}/u', $text, $m)) { $letters_upper = count($m[0]); } else { $letters_upper = 0; }

        // 4. количество знаков препинания
        if (preg_match_all('/\p{P}/u', $text, $m)) { $punct = count($m[0]); } else { $punct = 0; }

        // 5. количество цифр
        if (preg_match_all('/\p{Nd}/u', $text, $m)) { $digits = count($m[0]); } else { $digits = 0; }

        // 6. количество слов
        $words = [];
        if (preg_match_all('/[\p{L}\p{N}]+/u', $text, $wm)) {
            foreach ($wm[0] as $w) {
                $wl = u_strtolower($w);
                $words[$wl] = ($words[$wl] ?? 0) + 1;
            }
        }
        ksort($words, SORT_NATURAL | SORT_FLAG_CASE);
        $words_count = count($words);

        // 7. Частоты каждого символа (без учёта регистра)
        $sym_freq = [];
        $lowered = u_strtolower($text);
        // Разбиваем на массив символов 
        $chars = preg_split('//u', $lowered, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars !== false) {
            foreach ($chars as $ch) {
                $sym_freq[$ch] = ($sym_freq[$ch] ?? 0) + 1;
            }
        }
        ksort($sym_freq, SORT_NATURAL);
      ?>

      <section class="card">
        <h2>Информация о тексте</h2>
        <table class="table">
          <tbody>
            <tr><th>Количество символов (включая пробелы)</th><td><?= $char_count ?></td></tr>
            <tr><th>Количество букв</th><td><?= $letters_all ?></td></tr>
            <tr><th>Строчные буквы</th><td><?= $letters_lower ?></td></tr>
            <tr><th>Заглавные буквы</th><td><?= $letters_upper ?></td></tr>
            <tr><th>Знаки препинания</th><td><?= $punct ?></td></tr>
            <tr><th>Цифры</th><td><?= $digits ?></td></tr>
            <tr><th>Количество слов</th><td><?= $words_count ?></td></tr>
          </tbody>
        </table>
      </section>

      <section class="split">
        <div class="card">
          <h3>Частоты символов (без регистра)</h3>
          <table class="table">
            <thead><tr><th>Символ</th><th>Вхождений</th></tr></thead>
            <tbody>
              <?php foreach ($sym_freq as $ch => $cnt): ?>
                <tr>
                  <td><?= h($ch) === ' ' ? '&nbsp;␠ (пробел)' : h($ch) ?></td>
                  <td><?= $cnt ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="card">
          <h3>Слова по алфавиту</h3>
          <table class="table">
            <thead><tr><th>Слово</th><th>Вхождений</th></tr></thead>
            <tbody>
              <?php foreach ($words as $w => $cnt): ?>
                <tr><td><?= h($w) ?></td><td><?= $cnt ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
      <nav><a href="index.html">Другой анализ</a></nav>
    <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container">© ЛР10 · Никаева Марьям Руслановна · 241-362</div>
  </footer>
</body>
</html>
