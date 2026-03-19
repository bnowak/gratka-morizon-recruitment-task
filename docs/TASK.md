## Otrzymałeś zadanie, co teraz?

- Przeczytaj uważnie treść tego maila.

- Od razu stwórz repozytorium git. Modyfikacje kodu muszą być widoczne w historii zmian.



## Jak oceniamy zadanie?

Wiemy, jak trudne jest rozwiązywanie zadania rekrutacyjnego, gdy nie wiesz, jakie są kryteria oceny takiego zadania. W tym zadaniu zależy nam głównie na ocenie Twoich umiejętności. Nie będziemy oceniać tego w taki sam sposób, jak robimy to przy Code Review. Aczkolwiek nie traktuj zadania jako czegoś, co możesz odbębnić. Pracujemy na projektach, które rozwijane są od jakiegoś czasu i będą rozwijane jeszcze długo.

Wyobraź sobie, że ten projekt również będzie rozwijany przez kilka lat i przez kilku developerów.



Możesz wybrać ścieżkę KISS oraz opisać, co można by zrobić, by kod był łatwiejszy w rozbudowie. Lub możesz zastosować lekki overengineering, by pokazać w praktyce swoje umiejętności i opisać, dlaczego w tym przypadku nie warto.



Zależy nam na sprawdzeniu, czy znasz dobre praktyki programowania np. KISS, SOLID, DRY, TDD, F.I.R.S.T., Boy Scout Rule, i jak je stosujesz w praktyce. Jakie znasz techniki architektoniczne? Może da się tutaj coś poprawić?



Spodziewamy się, że napiszesz kilka testów. Nie cała aplikacja musi być przetestowana. Pochwal się tym, jakie testy znasz i napisz przynajmniej jeden test z każdego rodzaju.



## Twój komentarz

Twoimi przemyśleniami możesz dzielić się w `docs/NOTES.md`. Możesz zawrzeć tam np.



- Opis wprowadzonych zmian i podjętych decyzji architektonicznych

- Rzeczy, które zrobiłbyś inaczej mając więcej czasu

- Napotkane problemy i sposób ich rozwiązania

- Propozycje usprawnień, których nie zdążyłeś zaimplementować

- Informacje o sposobie i stopniu wykorzystania AI



Plik ten pomoże nam lepiej zrozumieć Twój tok myślenia.

Komentarze w kodzie też są okej.



## Zadanie

SymfonyApp to aplikacja, która pozwala użytkownikom na dzielenie się swoimi zdjęciami.

Jest we wczesnym etapie rozwoju i zawiera kilka podstawowych funkcjonalności.

Są to:

- Wyświetlanie galerii zdjęć na stronie głównej. Każdy kafelek zawiera podstawowe informacje oraz ilość polubień.

- Like/unlike zdjęć.

- Logowanie za pomocą tokenu oraz możliwość wylogowania.

- Wyświetlenie profilu.



### Zadanie 1 - zadbaj o jakość kodu oraz rozwiązań w projekcie SymfonyApp.

Znajdź błędy, a następnie nanieś poprawki lub zasugeruj zmianę.

Upewnij się, że projekt ma dobre fundamenty pod dalszy rozwój - struktura kodu musi być łatwa do zrozumienia dla nowych programistów.



### Zadanie 2 - Dodaj funkcjonalność importu zdjęć do SymfonyApp z PhoenixApi.

PhoenixApi to aplikacja, która przechowuje zdjęcia z innych aplikacji partnerskich, z których korzystają użytkownicy SymfonyApp. Wystawiony jest endpoint, za pomocą którego można pobrać zdjęcia używając tokenu dostępu.



W aplikacji SymfonyApp należy dać użytkownikom możliwość ręcznego wpisania tokenu dostępu do PhoenixApi (w profilu użytkownika). Token powinien zostać zapisany w bazie danych.

Następnie, po naciśnięciu przycisku "Importuj zdjęcia", zdjęcia z PhoenixApi powinny zostać zaimportowane do SymfonyApp jako zdjęcia tego użytkownika.

W przypadku błędnego tokenu, należy wyświetlić odpowiedni komunikat.



### Zadanie 3 - Filtrowanie zdjęć na stronie głównej.

Użytkownicy SymfonyApp muszą mieć możliwość filtrowania zdjęć po następujących polach:

- location

- camera

- description

- taken_at

- username



### Zadanie 4 - Zaimplementuj rate-limiting w aplikacji PhoenixApi.

Poszczególny użytkownik powinien móc importować swoje zdjęcia z PhoenixApi do SymfonyApp maksymalnie 5 razy na 10 minut. Oprócz tego, liczba wszystkich importów zdjęć może wynosić maksymalnie 1000 na godzinę. Jeśli znasz Elixira, spróbuj wykorzystać do tego OTP.
