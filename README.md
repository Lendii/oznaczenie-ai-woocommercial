# oznaczenie-ai-woocomercial

Darmowe narzędzie do WooCommerce, które oznacza główne zdjęcie produktu jako **AI GENERATED** albo **AI MODIFIED**. Pod kątem Art. 50 AI Act.

To jedno z narzędzi wspierających transparentność, nie gwarancja pełnej zgodności z AI Act i nie porada prawna.

## Co robi
- dodaje pole "Oznaczenie AI" w karcie produktu (zakładka Ogólne),
- pokazuje badge na zdjęciu na stronie produktu i w liście sklepu,
- dodaje krótki tekst informacyjny przy produkcie,
- badge dopasowuje się do fonta strony, kolory zmienia się w CSS.

## Wymagania
- WordPress z aktywnym WooCommerce,
- standardowy szablon produktu WooCommerce (przetestowane z motywem Kadence PRO).

## Instalacja
Plik `woocommerce-ai-label.php` (do pobrania jako `ai-content-label.php`) można wgrać na trzy sposoby:
1. FTP do `/wp-content/mu-plugins/` (bez aktywacji, widoczny w *Wtyczki → Must-Use*),
2. jako snippet PHP we wtyczce WPCode,
3. jako ZIP przez *Wtyczki → Dodaj nową → Wyślij wtyczkę*.

## Znane ograniczenia
- Własne szablony produktu (Elementor, Divi, Kadence Elements) mogą nie używać standardowych hooków. Wtedy badge może się nie pokazać i potrzebne jest dopasowanie.
- Brak eksportu oznaczenia do reklam i feedów.
- W liście sklepu badge ma tekst "AI", na stronie produktu pełny.

## Wersje
- **1.1**: guard `instanceof WC_Product`, `get_queried_object_id()` w `wp_footer`, własny text domain `ai-content-label`.
- **1.0**: wersja opublikowana.

## Do zrobienia
- test na motywach blokowych i Product Collection,
- sprawdzenie, czy WPCode przyjmuje kod zaczynający się od `<?php`,
- decyzja o licencji przed ewentualnym upublicznieniem repo.
