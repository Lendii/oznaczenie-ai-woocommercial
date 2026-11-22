# AI Content Label dla WooCommerce

Darmowy snippet do WooCommerce, który oznacza główne zdjęcie produktu jako **AI GENERATED** albo **AI MODIFIED**. Powstał pod kątem Art. 50 AI Act.

To snippet w jednym pliku, bez panelu ustawień: wgrywasz plik i działa. Nagłówek `Plugin Name` pozwala też zainstalować go jako paczkę ZIP.

To jedno z narzędzi wspierających transparentność, nie gwarancja pełnej zgodności z AI Act i nie porada prawna.

Strona z instrukcją wdrożenia dla właścicieli sklepów: [Darmowe oznaczenia AI dla WooCommerce](https://eastern-vole-f94.notion.site/Darmowe-oznaczenia-AI-dla-WooCommerce-3b0b9813a30380b28ce5fe545a4fee4e).

## Co robi
- dodaje pole "Oznaczenie AI" w karcie produktu (zakładka Ogólne),
- pokazuje badge na zdjęciu na stronie produktu i w liście sklepu,
- dodaje krótki tekst informacyjny przy produkcie,
- badge dopasowuje się do fonta strony, a kolory zmienia się w CSS.

## W jakich warunkach testowane
- **Środowisko testowe:** WordPress 7.1.5, WooCommerce 11.3.1, PHP 8.3, motyw Kadence PRO.
- **Wersja 1.2** sprawdzona ręcznie na tym sklepie: po ustawieniu oznaczenia przy produkcie badge wyświetla się na stronie produktu i w liście sklepu.
- Wcześniejsze wersje zainstalowane i sprawdzone na 4 stronach z Kadence PRO.
- Logika snippetu (zapis, walidacja, uprawnienia, wyjście hooków, podwójne załadowanie) ma test na atrapie WordPressa: `php tests/smoke-test.php`. Test nie zastępuje sprawdzenia na prawdziwym sklepie.
- **Nie testowane:** motywy blokowe (FSE), blok Product Collection, własne szablony produktu w page builderach.

## Wymagania
- PHP 7.4 lub nowszy,
- WordPress z aktywnym WooCommerce,
- standardowy szablon produktu WooCommerce.

## Instalacja
Plik `woocommerce-ai-label.php` można wgrać na trzy sposoby:
1. FTP do `/wp-content/mu-plugins/` (bez aktywacji, widoczny w *Wtyczki → Must-Use*),
2. jako snippet PHP we wtyczce WPCode,
3. jako ZIP przez *Wtyczki → Dodaj nową → Wyślij wtyczkę*.

## Bezpieczeństwo
- wartość z formularza przechodzi przez `sanitize_text_field` i listę dozwolonych wartości,
- zapis wymaga `current_user_can( 'edit_post' )`,
- nonce, autosave i uprawnienia sprawdza WooCommerce przed hookiem `woocommerce_process_product_meta` (`WC_Admin_Meta_Boxes::save_meta_boxes`, nonce `woocommerce_save_data`), więc snippet nie dubluje tej weryfikacji,
- meta zapisywana przez obiekt produktu WooCommerce (`update_meta_data` + `save_meta_data`),
- wyjście escapowane (`esc_html`, `wp_json_encode`).

## Znane ograniczenia
- Własne szablony produktu (Elementor, Divi, Kadence Elements) mogą nie używać standardowych hooków. Wtedy badge może się nie pokazać i potrzebne jest dopasowanie.
- Brak eksportu oznaczenia do reklam i feedów.
- W liście sklepu badge ma tekst "AI", na stronie produktu pełny.

## Wersje
- **1.2**: zapis i odczyt meta przez obiekt produktu WooCommerce, wspólna funkcja `ai_content_label_get()` zamiast powtórzonego kodu, ochrona przed podwójnym załadowaniem, `wp_json_encode` w skrypcie, usuwanie pustej meta.
- **1.1**: guard `instanceof WC_Product`, `get_queried_object_id()` w `wp_footer`, własny text domain `ai-content-label`.
- **1.0**: wersja opublikowana.

## Licencja
GPL-2.0-or-later, jak WordPress. Pełny tekst w pliku [LICENSE](LICENSE).

## Do zrobienia
- test na motywach blokowych i Product Collection,
- sprawdzenie, czy WPCode przyjmuje kod zaczynający się od `<?php`.
