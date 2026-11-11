<?php

/*
|--------------------------------------------------------------------------
| Manwal ng Gumagamit — Admin Panel
|--------------------------------------------------------------------------
|
| Ang kopya ng admin panel, naka-key sa mga paksang pinapangalanan ng
| catalogue sa `config/manual.php`. Ang bawat paksa ay isang `title` at ang
| mga `sections` nito; ang seksyon ay isang `title` at alinman sa `body`,
| `prerequisites`, `steps`, `result`, `fields` at `notes`. Ipi-print ng
| `partials/topic.blade.php` ang mga bloke sa nakapirming order na iyon.
|
| Ang front matter at ang mga kabanata ng tulong ay iniaalok sa bawat
| opisyal, kaya ang kopya ng mga ito ay hindi maaaring magpangalan ng tool
| na maaaring wala sa isang role.
|
*/

return [

    'title' => 'Manwal ng Gumagamit',
    'intro' => 'Paano patakbuhin ang THHA system, gawain sa bawat gawain. Ang mga tool na maaaring buksan ng iyong role lamang ang nakalista.',

    'groups' => [
        'getting_started'     => 'PAGSISIMULA',
        'operations'          => 'MGA OPERASYON',
        'finance'             => 'PANANALAPI',
        'reports_and_support' => 'MGA ULAT AT SUPORTA',
        'administration'      => 'PANGANGASIWA',
        'working_by_role'     => 'PAGTRABAHO AYON SA ROLE',
        'help'                => 'TULONG',
    ],

    'topics' => [

        'introduction' => [
            'title'    => 'Panimula',
            'sections' => [
                [
                    'title' => 'Para saan ang system',
                    'body'  => 'Ang THHA Information System ay ang opisina ng asosasyon sa isang browser. Iniingatan nito ang file ng homeowner (ang mga rekord ng RBI), nagsisingil at nangongolekta ng dues at iba pang bayarin, itinatala ang ginagastos ng asosasyon, may double-entry ledger para sa dalawa, tumatanggap ng booking ng amenities, nagtatala ng mga request, reklamo at paglabag na inihain ng mga homeowner, nagpi-print ng mga form ng asosasyon, at nag-uulat sa lahat ng ito.',
                ],
                [
                    'title' => 'Para kanino ito',
                    'body'  => 'Ang mga opisyal at kawani ng asosasyon ay nagtatrabaho sa admin panel, sa /admin. Ang mga homeowner ay pumapasok sa portal, sa /portal. Dalawang magkaibang uri ng account ito — ang login ng opisyal ay hindi login ng homeowner, at hindi mabubuksan ng isa ang mga pahina ng isa.',
                ],
                [
                    'title'  => 'Paano nakaayos ang system',
                    'body'   => 'Isang tuntunin ang nagpapaliwanag sa buong panel: ang sidebar ang index. Inililista nito ang bawat tool, pinangkat ayon sa lugar, at ang mga tool na maaaring buksan ng iyong role lamang ang iginuguhit.',
                    'fields' => [
                        'Sidebar'            => 'Ang mga tool na iniaalok sa iyo ng system, pinangkat sa ilalim ng mga header. Ang search box sa tuktok nito ay nakakahanap ng isa sa pamamagitan ng pangalan.',
                        'Navbar'             => 'Ang bar sa itaas. Ang hamburger ay nag-toggle ng sidebar, ang bell ay bumibilang ng iyong mga notification, at ang iyong pangalan ay nagbubukas ng iyong profile at ng sign-out.',
                        'Content area'       => 'Ang pahinang binuksan mo, may pamagat at — sa isang listahan — isang toolbar ng sariling aksyon ng pahina.',
                        'Footer'             => 'Ang sariling pangalan ng asosasyon at ang linya ng copyright.',
                        'Dashboard'          => 'Kung saan ka dumarating pagkatapos mag-sign in: ang mga pigurang mahalaga ngayon, ang taon sa mga chart, at lahat ng bukas pa.',
                    ],
                ],
                [
                    'title'  => 'Mga pangunahing konsepto',
                    'body'   => 'Anim na ideya ang nagpapadali sa pagbasa ng natitirang bahagi ng manwal. Ang mas buong kahulugan ay nasa Tulong → Talasalitaan.',
                    'fields' => [
                        'Resident'             => 'Isang taong na-profile na ng opisina. Ang bawat account ng homeowner ay naka-link sa isa lamang.',
                        'Account ng homeowner' => 'Ang portal login na ibinigay sa isang resident. Ito ang account na sinisingil ng asosasyon, hindi ang pangalan ng tao.',
                        'Bill'                 => 'Isang singil. Ang buwanang dues, invoice at bayad na booking ay lahat nagiging bill sa parehong ledger.',
                        'Bayad (Payment)'      => 'Perang natanggap laban sa isang bill. Ang bill ay nababayaran lamang mula sa mga resibong naitala laban dito.',
                        'Journal entry'        => 'Ang double-entry na rekord sa likod ng pagpasok o paglabas ng pera. Ang mga libro ang kabuuan ng mga ito.',
                        'Pahintulot (Permission)' => 'Isang switch sa isang role — "maaaring magdagdag ng resident", "maaaring mag-apruba ng request". Ang iyong role ang nagtatakda ng iyong mga menu.',
                    ],
                ],
            ],
        ],

        'getting-started' => [
            'title'    => 'Pagsisimula',
            'sections' => [
                [
                    'title'  => 'Pagbukas ng panel',
                    'body'   => 'Ang panel ay isang web address, hindi isang naka-install na programa: mabubuksan ito ng kahit anong kasalukuyang browser, mula sa opisina o sa bahay.',
                    'steps'  => [
                        'I-type ang address ng asosasyon sa address bar ng browser at idagdag ang /admin (halimbawa https://example.org/admin), pagkatapos pindutin ang Enter.',
                        'Kung hindi ka pa naka-sign in, bubukas ang pahina ng sign-in.',
                    ],
                    'result' => [
                        'Lalabas ang pahina ng sign-in, na humihingi ng iyong email address at password.',
                    ],
                    'notes'  => [
                        'I-bookmark ang address — ang eksaktong address ng opisina ay itinatakda ng asosasyon, hindi ng manwal na ito.',
                    ],
                ],
                [
                    'title'         => 'Paano mag-sign in',
                    'prerequisites' => [
                        'Isang account ng opisyal na ibinigay sa iyo ng asosasyon.',
                        'Ang address ng panel.',
                    ],
                    'steps'  => [
                        'Buksan ang pahina ng sign-in.',
                        'Ilagay ang email address at password na ibinigay sa iyong account.',
                        'Piliin ang Sign in.',
                        'Kung hinihingi ng opisina ang code para sa iyong role, i-type ang code na ipinadala sa email (tingnan ang susunod na seksyon).',
                    ],
                    'result' => [
                        'Bubukas ang dashboard.',
                        'Lalabas ang iyong pangalan sa kanan ng navbar, at ililista ng sidebar ang mga tool na maaaring buksan ng iyong role.',
                    ],
                    'notes'  => [
                        'Binibilang ang maling pagtatangka: pagkatapos ng masyadong marami nang sunod-sunod, hihinto sandali ang sign-in. Ang bilang at ang tagal ay itinatakda sa Pangangasiwa → Mga Setting → Security.',
                        'Ang deactivated na account ay tinatanggihan kahit tama ang password. Hilingin sa Administrator na i-reactivate ito.',
                    ],
                ],
                [
                    'title'         => 'Nakalimutang password',
                    'prerequisites' => [
                        'Access sa email address sa iyong account.',
                    ],
                    'steps'  => [
                        'Piliin ang Nakalimutan ang password? sa pahina ng sign-in.',
                        'Ilagay ang iyong email address at isumite.',
                        'Buksan ang email at sundan ang reset link.',
                        'Pumili ng bagong password na tumutugon sa mga tuntuning nakasaad sa form, at kumpirmahin ito.',
                    ],
                    'result' => [
                        'Sasagot ang form ng kumpirmasyon sa halip na ang iyong password, at ang bagong password ang magpapa-sign in sa iyo.',
                    ],
                    'notes'  => [
                        'Nag-e-expire ang reset link; humiling ng bago kung nag-expire na.',
                        'Ang mga tuntunin ng password ay mula sa Mga Setting → Security at kapareho ng ipinapatupad ng form ng account.',
                        'Ang account na ang password ay lumampas na sa expiry window ay itinatabi sa pahina ng profile nito hanggang sa mabago ang password.',
                    ],
                ],
                [
                    'title'  => 'Mabilis na paglibot sa screen',
                    'body'   => 'Lahat ng ginagawa ng panel ay naaabot mula sa tatlong lugar: ang sidebar, ang navbar at ang pahinang kinalalagyan mo.',
                    'fields' => [
                        'Mga header ng sidebar' => 'Ang mga lugar ng asosasyon: operasyon, pananalapi, mga ulat at suporta, pangangasiwa.',
                        'Search sa sidebar'     => 'I-type ang pangalan ng pahina at piliin ang resulta. Hinahanap nito ang menu na nakikita mo, kaya hindi ito nag-aalok ng pahinang hindi mo mabubuksan.',
                        'Navbar toggler'        => 'Kinokono ang sidebar pabalik sa mga icon at pabalik — kapaki-pakinabang sa maliit na screen.',
                        'Navbar bell'           => 'Ang iyong sariling mga notification: kung ano ang inihain, inaprubahan, binayaran o inisyu, pinakabago muna.',
                        'Iyong pangalan'        => 'Ang iyong menu: profile, at sign out.',
                        'Header ng pahina'      => 'Ang pamagat ng pahina, at sa tabi nito ang mga aksyon na naaangkop sa buong pahina.',
                        'Toolbar ng listahan'   => 'Search, mga filter, Add, Import at Export sa mga pahinang may listahan.',
                        'Mga aksyon (bawat row)' => 'Isang menu kada row — View, Edit, Delete, at ang sariling galaw ng rekord tulad ng Print o Approve.',
                        'Footer'                => 'Ang pangalan ng asosasyon, mula sa Mga Setting → General.',
                    ],
                    'steps'  => [
                        'Buksan ang isang pahina mula sa sidebar at panoorin ang pagbabago ng header kasama nito.',
                        'Buksan ang menu ng Aksyon ng isang row upang makita ang pinapayagan ng rekord na iyon.',
                    ],
                    'notes'  => [
                        'Halos lahat ng dialog ay agad na bubukas at kinukuha ang sariling rekord mula sa server habang bumubukas ito — kaya nagpapakita ang dialog ng maikling loading state.',
                    ],
                ],
                [
                    'title' => 'Pag-sign out',
                    'body'  => 'Buksan ang iyong pangalan sa kanan ng navbar at piliin ang Sign out. Sa shared computer, isara rin ang browser upang ang susunod na tao ay makita ang pahina ng sign-in sa halip na ang iyong session.',
                ],
            ],
        ],

        'dashboard' => [
            'title'    => 'Dashboard',
            'sections' => [
                [
                    'title' => 'Para saan ang dashboard',
                    'body'  => 'Ito ang landing page ng opisina: ang mga pigura ngayon, ang taon sa mga chart, at lahat ng bukas pa. Binabasa nito ang parehong mga serbisyong binabasa ng sarili nitong mga pahina ng ulat, kaya hindi maaaring magkasalungat ang pigura dito at ang pigura sa ulat.',
                ],
                [
                    'title'  => 'Pagbasa ng mga pangunahing pigura',
                    'body'   => 'Ang apat na kard sa itaas ay live na bilang, hindi naka-cache na placeholder.',
                    'fields' => [
                        'Mga homeowner'     => 'Bawat portal account na inisyu.',
                        'Bayad na bill'     => 'Bawat bill na may natanggap na pera laban dito, anuman ang nagtaas nito.',
                        'Bukas na request'  => 'Mga request na hindi pa napagpasyahan ng opisina.',
                        'Aktibong paglabag' => 'Mga paglabag na nananatili laban sa isang resident.',
                    ],
                    'notes'  => [
                        'I-click ang footer link ng kard upang tumalon sa listahan sa likod ng pigura. Lalabas lamang ang link kapag maaaring buksan ng iyong role ang listahang iyon.',
                    ],
                ],
                [
                    'title' => 'Ang taon sa mga chart',
                    'body'  => 'Mga homeowner ayon sa kasarian, Profit and Loss, Buwanang Kita at Buwanang Gastos, lahat para sa kasalukuyang taon. Iginuguhit ang mga ito mula sa mga nai-post na transaksyon, kaya gumagalaw ang mga ito habang kinokolekta ang mga bill, binabayaran ang mga gastos at nai-post ang mga entry.',
                ],
                [
                    'title' => 'Pagsubaybay',
                    'body'  => 'Ang Hindi Naresolbang Paglabag at mga Kahilingang Naghihintay ng Aksyon ay nasa ibaba ng mga chart.',
                    'steps' => [
                        'Gamitin ang View All sa alinman sa kard upang buksan ang buong rehistro.',
                        'Buksan ang isang row doon upang kumilos dito.',
                    ],
                ],
            ],
        ],

        'hoa-center' => [
            'title'    => 'HOA Center',
            'sections' => [
                [
                    'title'  => 'Ano ang nilalaman ng HOA Center',
                    'body'   => 'Ang sariling front matter ng asosasyon — kung sino ang naglilingkod, ano ang inaanunsyo, at ano ang ipinapakita ng pampublikong direktoryo. Lahat ng nasa loob nito ay ipinapalathala sa pampublikong site, kaya isulat ito para sa mambabasang nasa labas ng opisina.',
                    'fields' => [
                        'Mga HOA Member' => 'Ang listahan ng mga opisyal at miyembro ng komite, ipinapakita sa pampublikong pahina ng mga Miyembro.',
                        'Mga Anunsyo'    => 'Mga post na ipinapalathala ng asosasyon, na umaabot sa pampublikong site at sa portal ng homeowner kapag live na.',
                        'Direktoryo'     => 'Ang pampublikong listahan ng mga establishment at serbisyo sa subdivision, kasama ang mga pangkat at numero ng telepono.',
                    ],
                ],
                [
                    'title'         => 'Paano magpalathala ng anunsyo',
                    'prerequisites' => [
                        'Ang Create permission sa mga anunsyo.',
                    ],
                    'steps'  => [
                        'Buksan ang HOA Center → Mga Anunsyo at piliin ang Magdagdag ng Anunsyo.',
                        'Bigyan ito ng pamagat at ng nilalamang dapat basahin ng publiko.',
                        'I-save ito, pagkatapos ay i-publish.',
                    ],
                    'result' => [
                        'Lalabas ang anunsyo sa listahan, at — kapag na-publish — sa pampublikong site at sa portal.',
                    ],
                    'notes'  => [
                        'Ang hindi pa na-publish na anunsyo ay nananatiling draft: nakikita ito ng opisina at ng walang iba.',
                        'I-edit o itabi ang post kapag hindi na ito napapanahon sa halip na iwan ito.',
                    ],
                ],
                [
                    'title'  => 'Pagpapanatili ng listahan ng miyembro',
                    'steps'  => [
                        'Buksan ang HOA Center → Mga HOA Member.',
                        'Magdagdag ng miyembro kasama ang pangalan at posisyong dapat basahin ng publiko.',
                        'I-edit o tanggalin ang miyembrong natapos na ang termino.',
                    ],
                    'result' => [
                        'Binabasa ng pampublikong pahina ng mga Miyembro ang listahan kung paano ito naka-save — hindi maaaring magkasalungat ang opisina at ang publiko, dahil parehong rekord ang binabasa nila.',
                    ],
                ],
                [
                    'title'  => 'Pagpapanatili ng pampublikong direktoryo',
                    'steps'  => [
                        'Buksan ang HOA Center → Direktoryo.',
                        'Magdagdag ng entry: ang establishment o serbisyo, ang pangkat nito, at ang mga numerong ipinapalathala nito.',
                        'I-edit o tanggalin ang entry na nagsara na.',
                    ],
                    'notes'  => [
                        'Pampubliko ang direktoryo. Huwag ilagay dito ang pribadong numero ng homeowner — nasa rekord ng resident iyon.',
                    ],
                ],
            ],
        ],

        'homeowners' => [
            'title'    => 'Mga Homeowner',
            'sections' => [
                [
                    'title'  => 'Ano ang nilalaman ng lugar ng mga Homeowner',
                    'body'   => 'Ang mga tao at lugar na binabasa ng bawat iba pang module: ang mga address, ang mga lot at unit, ang mga resident na na-profile ng opisina, ang mga sambahayang kanilang tinitirhan, at ang mga portal account na ibinigay sa kanila. Ang pagsingil, booking, request at ulat ay nakakarating sa isang sambahayan sa pamamagitan ng mga rekord na ito, kaya panatilihing napapanahon ang mga ito.',
                    'fields' => [
                        'Mga Address'    => 'Bawat address ng kalye na nakatala. Ang property ay ginagawa laban sa isa.',
                        'Mga Property'   => 'Ang mga lot at unit ng subdivision, bawat isa nakaugnay sa isang address.',
                        'Mga Resident'   => 'Ang mga taong na-profile ng opisina. Ang RBI form at membership form ay ipi-print mula sa rekord na ito.',
                        'Mga Homeowner'  => 'Ang mga portal account: isa kada resident, na nag-uugnay sa tao sa isang login.',
                        'Mga Sambahayan' => 'Isang okupadong unit, ang sambahayang nakatira dito, at ang mga miyembro nito.',
                    ],
                ],
                [
                    'title'         => 'Paano magdagdag ng resident',
                    'prerequisites' => [
                        'Ang address (at property) na kinabibilangan ng tao — gawin muna ang mga iyon kung bago pa.',
                        'Ang Create permission sa mga resident.',
                    ],
                    'steps'  => [
                        'Buksan ang Mga Homeowner → Mga Resident at piliin ang Magdagdag ng Resident.',
                        'Punan ang personal na detalye na hinihingi ng form. Hindi maaaring iwang blangko ang mga required na field.',
                        'I-save ang rekord.',
                    ],
                    'result' => [
                        'Lalabas ang resident sa listahan ng mga Resident.',
                        'Maaari nang mapili ang rekord saanman pangalanan ng opisina ang isang resident — isang miyembro ng sambahayan, isang request, isang paglabag.',
                    ],
                    'notes'  => [
                        'Ang blangkong field ay blangko ang print: kinukuha ng RBI form ang mga halaga mula sa profile ng resident, kaya ang kulang na middle name o birth date ay lalabas sa printout.',
                        'Maaaring i-import at i-export ang mga resident nang maramihan mula sa toolbar ng listahan.',
                        'Maaaring i-print ng listahan ng mga Resident ang membership form para sa row sa ilalim ng Aksyon → Print.',
                    ],
                ],
                [
                    'title'         => 'Paano mag-isyu ng portal account',
                    'prerequisites' => [
                        'Ang resident na na-profile na.',
                        'Ang Create permission sa mga homeowner.',
                    ],
                    'steps'  => [
                        'Buksan ang Mga Homeowner at piliin ang Magdagdag ng Homeowner.',
                        'Piliin ang resident na pagmamay-ari ng account, at bigyan ito ng email address at unang password.',
                        'I-save, pagkatapos ay ibigay sa resident ang mga detalye ng sign-in.',
                    ],
                    'result' => [
                        'Lalabas ang resident sa listahan ng mga Homeowner at makakapag-sign in na sa portal.',
                    ],
                    'notes'  => [
                        'Maaaring may resident na walang account; ang account ay hindi maaaring magkaroon nang walang resident.',
                        'Ang pag-deactivate ng account ay humaharang sa sign-in at pinapanatili ang kasaysayan nito — hindi tinatanggal ang rekord.',
                        'Hindi nag-aalok ang asosasyon ng self-registration sa portal: dito iniisyu ang bawat account.',
                    ],
                ],
                [
                    'title'         => 'Paano magrekord ng sambahayan',
                    'prerequisites' => [
                        'Isang property, at isang account ng homeowner na hindi pa umaako ng ibang unit.',
                    ],
                    'steps'  => [
                        'Buksan ang Mga Homeowner → Mga Sambahayan at piliin ang Magdagdag ng Sambahayan.',
                        'Piliin ang account ng homeowner at ang property na okupado ng kanilang sambahayan.',
                        'I-save, pagkatapos ay idagdag ang mga miyembrong nakatira dito.',
                    ],
                    'result' => [
                        'Lalabas ang unit bilang okupado, at mababasa ang sambahayan at mga miyembro nito mula sa property at sa account.',
                    ],
                    'notes'  => [
                        'Isang sambahayan kada unit at isa kada account: ang account na may unit na ay hindi na iniaalok muli.',
                        'Maaaring i-import at i-export nang maramihan ang mga sambahayan at miyembro.',
                    ],
                ],
                [
                    'title'  => 'Pagbasa ng listahan',
                    'body'   => 'Ang bawat listahan sa panel — dito at sa mga module na susunod — ay may parehong tatlong bahagi, kaya isang paliwanag lang ang sapat para sa lahat.',
                    'fields' => [
                        'Toolbar'                => 'Search, mga filter at ang mga aksyon ng pahina (Add, Import, Export).',
                        'Mga tsek'               => 'Tsekan ang mga row na tinutukoy mo, pagkatapos gamitin ang selection bar na lalabas — halimbawa Delete Selected.',
                        'Mga aksyon (bawat row)' => 'Isang menu kada row: View, Edit, Delete, at ang mga galaw na pinapayagan ng rekord, tulad ng Print.',
                        'Chevrons / pager'       => 'Ang mahahabang listahan ay pumapahina; nasa ilalim ng talahanayan ang pager.',
                    ],
                    'notes'  => [
                        'Ang Import ay CSV at inaasahan ang template na iniaalok ng pahina — i-download muna ito, at panatilihin ang header row.',
                        'Ang Export ay nagsusulat ng eksaktong mga row na pinipili ng kasalukuyang mga filter.',
                    ],
                ],
            ],
        ],

        'print-documents' => [
            'title'    => 'Mga Dokumentong Ipi-print',
            'sections' => [
                [
                    'title'  => 'Ano ang mga naka-print na form',
                    'body'   => 'Ang mga nakapirming form at liham ng asosasyon. Ang bawat isa ay pinupunan mula sa mga rekord na nasa system na, kaya kakaunti lang ang itinatype ng opisina. Ang laki ng papel at ang letterhead ay mula sa Mga Setting → Print.',
                    'fields' => [
                        'RBI Form A'            => 'Ang household sheet: isang linya kada sambahayan, kasama ang tenure, tubig at kuryente.',
                        'RBI Form B'            => 'Ang individual sheet: profile ng isang resident, sambahayan at attestation.',
                        'RBI Form C'            => 'Ang semestral na buod ng mga sambahayan ng asosasyon ayon sa edad.',
                        'Membership Form'       => 'Ang aplikasyon ng membership, ipi-print mula sa rekord ng miyembro.',
                        'Listahan ng Resident'  => 'Bawat resident sa isang address, o ang buong asosasyon, bilang talahanayan.',
                        'Business Permit'       => 'Ang permit para sa negosyo ng resident sa loob ng subdivision.',
                        'HOA Permit'            => 'Ang permit para sa trabaho sa subdivision, na nagbibigay ng uri at saklaw nito.',
                        'Endorsement Letter'    => 'Isang liham na nag-eendorso ng resident sa isang pinangalanang tatanggap.',
                        'Certification'         => 'Isang sertipikasyon na nagsasaad ng layunin nito.',
                        'Invitation Letter'     => 'Ang imbitasyon, ipi-print para sa bawat homeowner o sa isang piniling pangkat.',
                        'Sinumpaang Salaysay'   => 'Ang sinumpaang salaysay.',
                        'Journal Voucher'       => 'Ang voucher para sa isang posting sa mga libro, ipi-print mula sa journal entry na iyon.',
                        'Violation Notice'      => 'Ang notice para sa naitalang paglabag.',
                    ],
                ],
                [
                    'title'         => 'Paano mag-print ng dokumento',
                    'prerequisites' => [
                        'Ang mga rekord na pinagbubuhatan ng dokumento — ang resident, ang address o ang journal entry — na nakatala na.',
                    ],
                    'steps'  => [
                        'Buksan ang dokumento mula sa menu ng Mga Dokumentong Ipi-print.',
                        'Punan ang mga field na hinihingi ng preview. Ang mga required na field ay minarkahan; tumatanggi ang form na gumawa ng dokumentong hindi nito mapupunan.',
                        'Tingnan ang preview.',
                        'Piliin ang Print para sa print dialog ng browser, o PDF upang i-download ang dokumento bilang file.',
                    ],
                    'result' => [
                        'Iginuguhit ng preview ang tapos na dokumento, kasama ang letterhead.',
                        'Ang PDF action ay nagda-download ng parehong dokumento, pinangalanan ayon sa dokumento at sa rekord na pinagbasehan.',
                    ],
                    'notes'  => [
                        'Mas maganda ang ilang dokumento sa mas malawak na papel: ang listahan ng resident ay ipi-print sa landscape.',
                        'Ang napakahabang dokumento ay hinahati-hati upang manatiling napi-print ang isang PDF, at sinasabi ito ng dokumento kapag huminto.',
                        'Ang pag-print ay naitala sa activity log, kaya ang isang inisyung notice ay maaaring ipaliwanag sa ibang pagkakataon.',
                    ],
                ],
            ],
        ],

        'amenities-booking' => [
            'title'    => 'Pag-book ng Amenities',
            'sections' => [
                [
                    'title'  => 'Ano ang nilalaman ng booking module',
                    'body'   => 'Ang mga karaniwang pasilidad ng subdivision at ang mga reserbasyon laban sa mga ito — nasa homeowner man ang pag-book mula sa portal o tinanggap ng opisina sa telepono.',
                    'fields' => [
                        'Mga Booking'    => 'Bawat reserbasyon, kasama ang petsa at oras, ang resident, ang bayad at ang depositong dala nito.',
                        'Calendar View'  => 'Ang parehong mga reserbasyon na inayos ayon sa petsa, kaya kitang-kita agad ang dobleng booking.',
                        'Mga Amenity'    => 'Ang mga pasilidad mismo: rate, lingguhang iskedyul, tuntunin ng deposit, at kung iniaalok ito ng portal.',
                    ],
                ],
                [
                    'title'         => 'Paano magdagdag o magbago ng amenity',
                    'prerequisites' => [
                        'Ang Create o Edit permission sa mga amenity.',
                    ],
                    'steps'  => [
                        'Buksan ang Pag-book ng Amenities → Mga Amenity at piliin ang Magdagdag ng Amenity.',
                        'Pangalanan ito, at itakda kung paano ito sisingilin: flat rate, o hourly rate na pinaparami sa mga oras na ni-book.',
                        'Itakda ang lingguhang iskedyul. I-off ang isang araw ng linggo upang isara ito — ang saradong araw ay walang oras.',
                        'Itakda ang tuntunin ng deposit, at kung maaaring i-book ito ng mga homeowner mula sa portal.',
                        'I-save.',
                    ],
                    'result' => [
                        'Lalabas ang amenity sa listahan at, kung pinapayagan ang portal, sa booking form ng homeowner.',
                    ],
                    'notes'  => [
                        'Ang amenity na zero o blangko ang rate ay libre: ang libreng booking ay hindi umaabot sa payment step.',
                        'Ang booking na mayroon na ay pinapanatili ang rate na ipinresyo dito — ang pagbabago ng amenity ay hindi muling nagpepresyo nito.',
                    ],
                ],
                [
                    'title'         => 'Paano inaaksyunan ng opisina ang booking',
                    'prerequisites' => [
                        'Ang Edit permission sa mga booking.',
                    ],
                    'steps'  => [
                        'Buksan ang Pag-book ng Amenities → Mga Booking at hanapin ang reserbasyon.',
                        'Buksan ang menu ng Aksyon nito. Ang mga galaw na iniaalok ay ang mga naaangkop pa sa booking na iyon.',
                        'Aprubahan ito upang tanggapin ang slot.',
                        'Kunin ang bayad para sa booking na may halaga, sa pamamagitan ng sariling payment form ng ledger.',
                        'Kumpletuhin ito kapag nagamit na ang amenity.',
                        'I-refund o iforfeit ang deposit kung mayroon at hindi natugunan ng sambahayan ang mga kondisyon.',
                    ],
                    'result' => [
                        'Dumadaan ang booking sa mga estado nito at pareho ang sinasabi ng listahan, ng kalendaryo at ng pahina ng homeowner.',
                        'Ang invoice ng booking ay lumalabas sa Koleksyon → Mga Invoice at kinokolekta doon gaya ng anumang bill; sinusundan ng mga linya nito ang reserbasyon hanggang may maitalang bayad.',
                    ],
                    'notes'  => [
                        'Ang pag-edit ng hindi pa bayad na booking ay muling nagpepresyo ng invoice nito; kapag may naitalang bayad, ang invoice na ang makasaysayang bill of record.',
                        'Ang libreng booking ay isinasara bilang nagamit kaagad mula sa pag-apruba — wala nang kokolektahin.',
                        'Ang homeowner ay maaaring mag-withdraw ng booking habang pending pa; pagkatapos nito, ang opisina ang may kontrol.',
                        'Ang bawat galaw ay muling sinusuri ang booking habang bumubukas ito, kaya ang pahinang iniwan bukas magdamag ay hindi makakapag-trigger ng galaw na hindi na naaangkop.',
                    ],
                ],
            ],
        ],

        'collections' => [
            'title'    => 'Koleksyon',
            'sections' => [
                [
                    'title'  => 'Ano ang saklaw ng pagsingil at mga resibo',
                    'body'   => 'Ang panig ng kita ng asosasyon. Ang buwanang dues, isahang invoice at bayad na booking ay lahat bill; ang bawat resibo — kinuha sa counter o inihain mula sa portal — ay bayad laban sa isa sa mga ito.',
                    'fields' => [
                        'Mga Invoice'               => 'Mga singil na itinataas ng opisina nang manu-mano, para sa isang resident, unit o booking.',
                        'Buwanang Dues'             => 'Ang paulit-ulit na dues, na ginawa para sa isang buwan, kasama ang talahanayan ng dues at ang generator sa likod nito.',
                        'Mga Bayad'                 => 'Ang nag-iisang ledger ng resibo, kasama ang mga bayad na inihain ng homeowner na naghihintay pa ng apruba.',
                        'Mga Advance Deposit'       => 'Kredito na hawak ng asosasyon para sa isang account, ibinabawas laban sa mga susunod na bill.',
                        'Mga Produkto at Serbisyo'  => 'Ang mga sisingiling bagay na pinagbubuhatan ng mga invoice at booking, pinangkat ayon sa kategorya.',
                        'Email SOA'                 => 'Mga statement of account na ipinadala sa mga homeowner, at ang rekord ng ipinadala.',
                    ],
                ],
                [
                    'title'         => 'Paano magtaas ng isahang invoice',
                    'prerequisites' => [
                        'Ang account ng sambahayan, at ang produkto o halagang sisingilin.',
                    ],
                    'steps'  => [
                        'Buksan ang Koleksyon → Mga Invoice at piliin ang Magdagdag ng Invoice.',
                        'Piliin ang account na sisingilin at itakda ang petsa ng pagsingil.',
                        'Idagdag ang mga linya: ang produkto o serbisyo, ang dami at ang presyo.',
                        'I-save, pagkatapos ay tingnan ang kabuuang ipinapakita ng form bago ito gawin.',
                    ],
                    'result' => [
                        'Lalabas ang invoice sa listahan na may balanseng katumbas ng kabuuan nito.',
                        'Lalabas ito sa Statement of Account ng sambahayan sa portal.',
                    ],
                    'notes'  => [
                        'Ang linya ay pinapanatili ang pangalan at presyo na sinisingil nito, kaya ang pagpapalit ng pangalan ng produkto sa susunod ay hindi muling nagsusulat ng inisyung invoice.',
                        'Ang bill ay perang utang, hindi kita: nakikilala ang kita kapag nai-post ang resibo laban dito.',
                        'Ang bill na may natanggap nang pera ay kinakansela, hindi kailanman tinatanggal.',
                    ],
                ],
                [
                    'title'         => 'Paano patakbuhin ang buwanang dues',
                    'prerequisites' => [
                        'Ang mga rate ng dues at araw ng pagbabayad na itinakda sa Mga Setting → Collections.',
                    ],
                    'steps'  => [
                        'Buksan ang Koleksyon → Buwanang Dues at tingnan ang buwan na sisingilin mo palang.',
                        'Suriin ang generator: inililista nito ang mga unit na dapat singilin at ang rate ng bawat isa.',
                        'Patakbuhin ito upang itaas ang mga dues.',
                        'Basahin ang talahanayan ng dues para sa buwan at iwasto ang anumang mali — i-adjust ang invoice o i-void ito — pagkatapos ay i-generate muli.',
                    ],
                    'result' => [
                        'Isang dues bill kada unit para sa buwang iyon, bawat isa may dalang rate na ipinresyo nito.',
                    ],
                    'notes'  => [
                        'Ang isang buwan ay ginagawa nang isang beses: ang unit na naisingil na para sa buwan ay hindi na sisingilin muli.',
                        'Kinokopya ng due ang configuration ng rate nito sa sarili nito, kaya ang pagbabago ng rate sa susunod ay hindi muling nagpepresyo ng buwan na naisingil na.',
                        'Ang kreditong hawak para sa isang account ay kusang ibinabawas sa buwan habang itinataas ang mga dues.',
                    ],
                ],
                [
                    'title'         => 'Paano magrekord ng bayad na kinuha sa opisina',
                    'prerequisites' => [
                        'Ang bill na binabayaran.',
                    ],
                    'steps'  => [
                        'Buksan ang Koleksyon → Mga Bayad at piliin ang Magrekord ng Bayad.',
                        'Piliin ang bill na binabayaran ng pera.',
                        'Ilagay ang halagang natanggap, ang paraan, ang petsa at ang reference.',
                        'I-save.',
                    ],
                    'result' => [
                        'Naitatala ang resibo at muling kinukwenta ng bill ang balanse nito mula sa mga resibo nito.',
                        'Ang ganap na nabayarang bill ay mababasa bilang bayad saanman ito nakalista.',
                    ],
                    'notes'  => [
                        'Ang bill na may bayad nang inihain mula sa portal ay nagpapakita ng naghihintay na resibo: tinatanggihan ng form ang pangalawang resibo at pinapangalanan ang naghihintay. Aprubahan o tanggihan muna ang isang iyon.',
                        'Karaniwan ang bahagyang pagbabayad: itala ang ibinigay at mananatili ang balanse para sa natitira.',
                        'Ang pagrekord ng bayad ay hindi humahawak sa mga libro. Ang accounting ang nagpo-post nito mula sa row ng Mga Bayad gamit ang Post to Journal, at pinipili ang account na pinasukan ng pera.',
                    ],
                ],
                [
                    'title'         => 'Paano aprubahan ang bayad na inihain mula sa portal',
                    'prerequisites' => [
                        'Lalabas ang resibo sa listahan ng Mga Bayad bilang pending.',
                    ],
                    'steps'  => [
                        'Buksan ang Koleksyon → Mga Bayad at hanapin ang pending na resibo.',
                        'Buksan ito at ihambing ang patunay ng bayad na ikinabit ng homeowner sa halaga at sa reference.',
                        'Aprubahan ito upang irekord, o tanggalin ito upang tanggihan upang makapaghain muli ang homeowner.',
                    ],
                    'result' => [
                        'Ang inaprubahang resibo ay binibilang laban sa bill at binabayaran ang saklaw nito.',
                        'Ipinapakita ito ng pahina ng Aking mga Bayad ng homeowner bilang naitala.',
                    ],
                    'notes'  => [
                        'Ang pending na resibo ay walang binabayaran at hindi umaabot sa mga libro; ang apruba ay nagtatala ng pera, at hiwalay na hakbang ng accounting ang pag-post ng entry.',
                        'Dahil iniiawan ng pending na resibo na nakatayo ang bill, hindi dapat kunin ng opisina ang parehong pera sa counter habang naghihintay ito.',
                    ],
                ],
                [
                    'title' => 'Mga advance deposit at produkto',
                    'body'  => 'Ang kreditong hawak sa isang account ay naitatala bilang advance deposit, laban sa account na pinili ng opisyal at hindi sa isang unit. Ang mga produkto ay ang catalogue na pinagbubuhatan ng mga invoice at booking, at ang bawat isa ay kabilang sa isang kategorya na nagtatakda kung aling account ng kita ang kinikitaan ng pera.',
                    'notes' => [
                        'Ang produkto o paraang itinutukoy ng isang rekord ay itinatabi sa halip na tinatanggal, kaya nananatiling nababasa ang mga lumang invoice.',
                        'Ang pagtanggal ng produkto ay hindi naaapektuhan ang mga invoice na may pangalan na nito.',
                    ],
                ],
                [
                    'title'         => 'Paano mag-email ng statement of account',
                    'prerequisites' => [
                        'Ang mga account ay may email address at kahit isang bukas na bill.',
                    ],
                    'steps'  => [
                        'Buksan ang Koleksyon → Email SOA.',
                        'Ihanda ang batch: inililista nito ang mga account na may isasabi.',
                        'I-preview ang isa upang tingnan ang letterhead at ang listahan ng mga bukas na bill.',
                        'Ipadala ang batch at basahin ang rekord ng ipinadala.',
                    ],
                    'result' => [
                        'Ang bawat sambahayan ay tumatanggap ng isang statement na naglilista ng kanilang bukas na dues at mga singil nang sama-sama.',
                    ],
                    'notes'  => [
                        'Ang statement ay isang talahanayan: ang paulit-ulit na dues at isahang singil ay nakalista nang sama-sama sa halip na hiwalay.',
                        'Ligtas ang muling pagpapadala at naitatala, kaya nakikita ng opisina kung ano ang sinabi sa sambahayan at kailan.',
                    ],
                ],
            ],
        ],

        'expenses' => [
            'title'    => 'Mga Gastos',
            'sections' => [
                [
                    'title'  => 'Ano ang saklaw ng mga pahina ng gastos',
                    'body'   => 'Ang mga gastusin ng asosasyon at ang mga catalogue sa likod ng mga ito.',
                    'fields' => [
                        'Mga Gastos'             => 'Perang ginastos. Ang gastos ay ginagawang draft, pagkatapos ay inaprubahan — at hiwalay na ipinopost ito ng accounting sa mga libro.',
                        'Mga Kategorya ng Gastos' => 'Ang mga heading na pinapangkatan ng mga gastos. Ang kategorya ay nagtatakda kung aling account nagpo-post ang gastos.',
                        'Mga Supplier'           => 'Ang mga pagtanggap na maaaring pangalanan ng isang gastos.',
                    ],
                ],
                [
                    'title'         => 'Paano magrekord ng gastos',
                    'prerequisites' => [
                        'Ang supplier at ang kategorya na nakatala na.',
                    ],
                    'steps'  => [
                        'Buksan ang Mga Gastos at piliin ang Magrekord ng Gastos.',
                        'Piliin ang supplier at ang mga petsang hinihingi ng wizard.',
                        'Idagdag ang mga linya: ang binili, ang dami at ang presyo.',
                        'I-save bilang draft.',
                        'Buksan ito at aprubahan kapag napagkasunduan na ang mga pigura.',
                    ],
                    'result' => [
                        'Dumadaan ang gastos sa Draft → Aprubado, at sinusundan ito ng listahan at ng mga kabuuan nito.',
                        'Pagkatapos, ipinopost ito ng accounting mula sa row ng gastos gamit ang Post to Journal, at pinipili ang account na pinagmulan ng pera.',
                    ],
                    'notes'  => [
                        'Ang draft ang pending na estado — walang kailangang ipadala kahit saan para maghintay ng apruba ang isang gastos.',
                        'Ang linya ay pinapanatili ang pangalan ng kategorya na pinagpilian nito, kaya ang pagtanggal ng kategorya ay hindi muling nagsusulat ng lumang gastos.',
                        'Draft lang ang maaaring i-edit. Ang pagtanggal ng naka-post na gastos ay nagpo-post muna ng reversal nito, kaya pareho itong nananatili sa mga libro; ang aprubadong gastos na walang entry ay basta tinatanggal.',
                    ],
                ],
                [
                    'title'  => 'Mga kategorya at supplier',
                    'body'   => 'Pareho itong catalogue na pinapanatiling maayos ng opisina: ang kategorya ay heading pa rin na pinagpilian ng lumang gastos, at ang supplier ay pagtanggap pa rin na pinangalanan ng lumang gastos.',
                    'notes'  => [
                        'Ang kategorya o supplier na ginagamit na ng mga gastos ay hindi maaaring tanggalin.',
                        'Ang kategoryang walang sariling account ay bumalik sa unclassified account.',
                    ],
                ],
            ],
        ],

        'accounting' => [
            'title'    => 'Accounting',
            'sections' => [
                [
                    'title'  => 'Ano ang mga libro',
                    'body'   => 'Ang double-entry ledger na pinopostan ng bawat money module. Walang nag-iimbak ng balanse: ang bawat pigura ay hinango mula sa mga linyang nai-post, kaya ang mga libro at ang mga ulat na gawa mula sa mga ito ay laging nagkakasundo.',
                    'fields' => [
                        'Chart of Accounts'   => 'Ang mga account na pinagpapatungan ng mga libro. Ang mga singil at resibo ay kusang nagpo-post sa mga ito.',
                        'Mga Journal Entry'   => 'Ang journal mismo: bawat posting, kasama ang voucher nito.',
                        'Mga Transfer'        => 'Pera na inilipat sa pagitan ng mga account sa labas ng bill o gastos.',
                        'Mga Transaksyon'     => 'Isang read-only na feed ng bawat posting line, kapaki-pakinabang sa pagsubaybay kung saan nanggaling ang isang balanse.',
                        'Period Close'        => 'Isang buwang isinara upang walang makapag-post pa dito, maaaring buksang muli kapag hindi maiiwasan ang pagwawasto.',
                    ],
                ],
                [
                    'title'  => 'Ang chart of accounts',
                    'body'   => 'Ang chart ay pinangkat sa Income, Expenses at Cash.',
                    'steps'  => [
                        'Buksan ang Accounting → Chart of Accounts upang basahin ang mga account, ang kita at gastos ngayong taon, at ang laman ng bawat cash account.',
                        'Magdagdag ng account sa pamamagitan ng pangalan at kind — may iminumungkahing code at maaari mo itong palitan. Ang code ay 3 hanggang 6 na digit at kailangang hindi pa ginagamit.',
                        'Ang cash account ay nagtatanong kung cash o nasa bangko ang pera, at kung magkano ang hawak nito sa petsa ng simula.',
                        'Palitan ang pangalan o kind ng account habang wala pang naka-post dito; ang account na pagmamay-ari ng kategorya ay pinapalitan ng pangalan sa pahina ng kategorya.',
                    ],
                    'result' => [
                        'Lalabas ang account sa pangkat nito kasama ang halaga, zero hanggang may mag-post dito. Ang starting balance ay itinatala bilang perang galing sa labas ng asosasyon.',
                    ],
                    'notes'  => [
                        'Ang account ay maaaring tanggalin lamang habang wala pang naka-post dito at walang kategorya, produkto, paraan ng pagbabayad o transfer na tumutukoy dito. Kung hindi, sasabihin ng chart kung bakit at pananatilihin ito.',
                        'Ang ilang anchor account ay ginagawa para sa iyo sa unang pagkakataong kailanganin ng isang posting, kaya ang bagong install ay hindi kailanman nagpo-post sa wala.',
                    ],
                ],
                [
                    'title'  => 'Ang journal',
                    'body'   => 'Ang bill na itinaas ay hindi posting: nakikilala ang kita kapag ipinost ng accounting ang resibong kumokolekta nito. Ang aprubadong gastos ay ipinopost sa parehong paraan, mula sa row ng gastos. Ang journal entry ay may isang halaga, isang account na tatanggap at isang account na pagmumulan, kaya pantay ang dalawang panig nito.',
                    'steps'  => [
                        'Buksan ang Accounting → Mga Journal Entry at hanapin ang posting na gusto mo.',
                        'Tingnan ito upang basahin ang Goes To at Comes From, o i-print ang voucher nito mula sa menu ng Aksyon ng row.',
                        'I-edit ang manual entry upang iwasto ang halaga, account o memo. Ang entry mula sa payment, gastos o transfer ay pinapanatili ang halaga at account na naitala ng pinagmulan; maaari pa ring linawin ang memo.',
                        'Tanggalin ang manual entry mula sa edit window nito kapag hindi ito dapat manatili. Ang entry ng module ay tinatanggal kasama ng pinagmulan nito.',
                    ],
                    'result' => [
                        'Pinapanatili ng entry ang pantay na panig at pareho ang mababasa sa pahina, sa view window at sa voucher.',
                    ],
                    'notes'  => [
                        'Ang voucher ang napi-print na rekord ng entry at ipi-print ang parehong mga pangalan ng account na ipinostan ng entry.',
                        'Ang saradong buwan ay tumatanggi sa bagong entry, edit at pagtanggal hanggang muling buksan.',
                    ],
                ],
                [
                    'title' => 'Mga transfer at ang rehistro',
                    'body'  => 'Ang transfer ay naglilipat ng pera sa pagitan ng dalawang account na walang bill o gastos sa likod nito — halimbawa isang bank deposit. Ang rehistro ng transaksyon ay naglilista ng bawat linya, read-only, para sa pagsubaybay.',
                ],
                [
                    'title'  => 'Paano magsara ng panahon',
                    'steps'  => [
                        'Buksan ang Accounting → Period Close at hanapin ang buwan.',
                        'Isara ito.',
                        'Buksan muli lamang kung ang pagwawasto ay hindi na makapaghihintay sa susunod na panahon.',
                    ],
                    'result' => [
                        'Ang saradong buwan ay hindi na tumatanggap ng karagdagang posting; sinasabi ito ng rehistro.',
                    ],
                    'notes'  => [
                        'Ang pagsasara ay kada buwan at naitatala, kaya masasagot kung sino ang nagsara ng ano at kailan.',
                    ],
                ],
            ],
        ],

        'supports' => [
            'title'    => 'Suporta',
            'sections' => [
                [
                    'title'  => 'Ano ang nilalaman ng mga pahina ng Suporta',
                    'body'   => 'Ang tatlong rekord na inaayos ng asosasyon tungkol sa mga homeowner nito: mga request para sa dokumento o serbisyo, mga reklamo tungkol sa subdivision, at mga paglabag sa mga tuntunin nito.',
                    'fields' => [
                        'Mga Request'   => 'Mga paghahain ng homeowner para sa isa sa limang dokumento. Ang request ay pending hanggang aprubahan o tanggihan ito ng opisina.',
                        'Mga Reklamo'   => 'Mga alalahanin na inihain ng homeowner, kasama ang sariling rekord ng opisina kung paano hinarap ang bawat isa.',
                        'Mga Paglabag'  => 'Mga paglabag sa tuntuning naitala laban sa isang resident, bawat isa may napi-print na notice.',
                    ],
                ],
                [
                    'title'         => 'Paano aksyunan ang isang request',
                    'prerequisites' => [
                        'Ang Edit permission sa mga request upang magpasya sa isa.',
                    ],
                    'steps'  => [
                        'Buksan ang Suporta → Mga Request at hanapin ang paghahain.',
                        'Tingnan ito upang basahin kung ano ang hiniling at kanino.',
                        'Aprubahan ito upang isyu ang dokumento, o tanggihan ito — dapat sabihin ng pagtanggi kung bakit.',
                        'Itaas ang invoice gamit ang Create Invoice kapag babayaran ang dokumento.',
                        'Buksan ang Mga Bayad sa row upang makita kung nagbayad na ang sambahayan, at upang aprubahan ang resibong inihain ng portal.',
                        'I-print ang dokumento kapag naaprubahan na ang request.',
                    ],
                    'result' => [
                        'Ang inaprubahang request ay nagpapakita ng Print action nito at hindi na nag-aalok ng Edit, Approve at Decline.',
                        'Ang tinanggihang request ay nagtatala ng dahilan at nagsasara.',
                    ],
                    'notes'  => [
                        'Ang napagpasyang request ay hindi kailanman muling isinusulat: ang mga galaw na natitira sa isang row ay sumusunod sa sarili nitong status, hindi sa mambabasa.',
                        'Ang Create Invoice ay nangangailangan ng portal account ng sambahayan; ang resident na walang account ay hindi maaaring sisingilin, at sinasabi ito ng row.',
                        'Kapag na-invoice na ang request, mababasa ang row bilang Invoiced at hindi na nag-aalok ng ibang invoice, kaya hindi kailanman sisingilin nang dalawang beses ang parehong request.',
                    ],
                ],
                [
                    'title' => 'Paano nakakarating sa homeowner ang dokumento ng request',
                    'body'  => 'Ang pag-apruba ng request ay nag-isyu ng dokumento nito at nagbubukas ng window kung saan maaari itong i-download. Ang tuntunin ng paglabas ay nakasaad, hindi hinuhulaan: dapat aprubahan ang request, dapat bayaran ang invoice na itinaas para dito, dapat bukas pa ang window, at hindi dapat naubos ang badyet ng download.',
                    'notes' => [
                        'Ipinapaliwanag ng kard ng portal ang bawat pagpigil — hindi pa bayad ang invoice, sarado ang window, naubos ang badyet.',
                        'Ang dokumentong nangangailangan ng mga halagang hindi nakolekta ng request ay tinatanggihan sa halip na i-print na kalahating blangko.',
                    ],
                ],
                [
                    'title'  => 'Paano magrekord ng paglabag',
                    'steps'  => [
                        'Buksan ang Suporta → Mga Paglabag at piliin ang Magdagdag ng Paglabag.',
                        'Piliin ang resident, ang uri ng paglabag at ang petsang nangyari ito.',
                        'Itala ang naobserbahan.',
                        'I-print ang notice mula sa menu ng Aksyon ng row at ihain ito.',
                        'Resolbahin ang paglabag kapag naayos na.',
                    ],
                    'result' => [
                        'Lalabas ang paglabag sa pahina ng portal ng resident, at naabisuhan ang sambahayan.',
                        'Nagda-download ang notice bilang PDF na gawa mula sa letterhead ng asosasyon.',
                    ],
                    'notes'  => [
                        'Ang mga hindi pa naresolbang paglabag lamang ang binibilang sa dashboard at sa ulat ng komunidad.',
                    ],
                ],
                [
                    'title'  => 'Paano sagutin ang reklamo',
                    'steps'  => [
                        'Buksan ang Suporta → Mga Reklamo at hanapin ang paghahain.',
                        'Tingnan ito, pagkatapos ay Tumugon kasama ang ginawa ng opisina tungkol dito.',
                        'I-edit ang paghahain na mismong ipinasok ng opisina, o tanggalin ang isang inihain nang mali.',
                    ],
                    'result' => [
                        'Ipinapakita ng reklamo ang status nito at ang mga puna ng opisina, na siyang binabasa ng homeowner.',
                    ],
                ],
            ],
        ],

        'reports' => [
            'title'    => 'Mga Ulat',
            'sections' => [
                [
                    'title'  => 'Ano ang mga ulat',
                    'body'   => 'Ang read-only na pag-uulat ng asosasyon, ang bawat pigura ay hinango mula sa module na nagmamay-ari nito kaya hindi maaaring magkasalungat ang isang ulat sa pahinang nilalagom nito.',
                    'fields' => [
                        'Profit & Loss'        => 'Kita laban sa mga gastos para sa isang panahon.',
                        'Cash Flow'            => 'Pera na pumapasok at lumalabas, ayon sa aktibidad.',
                        'AR Aging'             => 'Ang utang ng mga homeowner sa asosasyon, ayon sa edad.',
                        'Buwanang Kita / Gastos' => 'Ang dalawang paggalaw buwan-buwan.',
                        'Daily Collection, Daily Income, Daily Expenses' => 'Ang mga rehistro ng araw: ang nakolekta, kinita at ginastos.',
                        'Statement of Account' => 'Ang mga bukas na bill ng isang sambahayan, handang ibigay o ipadala.',
                        'Komunidad'            => 'Ang subdivision sa mga numero — populasyon, sambahayan, booking at ang kinita ng mga ito.',
                        'Mga Resident'         => 'Ang populasyon ng resident at ang halo nito.',
                    ],
                ],
                [
                    'title'  => 'Paano magpatakbo at mag-export ng ulat',
                    'steps'  => [
                        'Buksan ang ulat mula sa menu ng Mga Ulat.',
                        'Itakda ang panahon o ang mga filter na iniaalok ng ulat.',
                        'Basahin ang mga pigura; muling iginuguhit ang mga chart kasama ng mga ito.',
                        'I-export ang talahanayan kung kailangan mo ito sa spreadsheet o para sa pulong.',
                    ],
                    'result' => [
                        'Iginuguhit ng ulat ang mga row na pinipili ng mga filter, kasama ang mga kabuuan sa ilalim ng talahanayan.',
                        'Nagda-download ang Export ng CSV ng eksaktong mga row at column na iyon.',
                    ],
                    'notes'  => [
                        'Ang mga ulat ng Komunidad at Mga Resident ay ilang distribusyon nang sabay, kaya walang export ang mga ito.',
                        'Ang ulat ay hindi kailanman nagsusulat ng anuman: ang pagpapatakbo ng isa ay hindi ginagalaw ang mga libro.',
                    ],
                ],
            ],
        ],

        'user-management' => [
            'title'    => 'Pamamahala ng User',
            'sections' => [
                [
                    'title'  => 'Mga account at role ng opisyal',
                    'body'   => 'Ang bawat opisyal at kawani ay nag-sign in gamit ang sariling account, at ang bawat account ay may eksaktong isang role. Ang role ang nagdadala ng mga permission: baguhin ang isang role at magbabago ang bawat opisyal na mayroon nito.',
                    'fields' => [
                        'Mga User'    => 'Ang mga account ng opisyal at kawani para sa panel, kasama ang kanilang role at kung aktibo sila.',
                        'Mga Role'    => 'Ang mga switch ng permission na dala ng isang role, at ang mga opisyales na mayroon nito.',
                        'System role' => 'Isang role na pinapatakbo mismo ng software. Hindi ito maaaring alisin, at ang role na Administrator ay laging may buong catalogue.',
                    ],
                ],
                [
                    'title'         => 'Paano magdagdag ng opisyal',
                    'prerequisites' => [
                        'Ang role na hahawakan ng opisyal — gawin muna ito kung bago.',
                    ],
                    'steps'  => [
                        'Buksan ang Pangangasiwa → Mga User at piliin ang Magdagdag ng User.',
                        'Ibigay sa opisyal ang kanilang pangalan at ang email address na gagamitin sa sign-in.',
                        'Piliin ang kanilang role at itakda ang unang password na tumutugon sa nakasaad na tuntunin.',
                        'I-save, pagkatapos ay ibigay ang mga detalye ng sign-in at hilingin sa opisyal na baguhin ang password.',
                    ],
                    'result' => [
                        'Lalabas ang account sa listahan at makakapag-sign in agad.',
                    ],
                    'notes'  => [
                        'Ang account ay may eksaktong isang role: ang taong nangangailangan ng dalawang uri ng access ay nangangailangan ng isang role na nagdadala ng pareho.',
                        'Itinatala ng activity log kung ano ang ginawa ng bawat opisyal, kaya maaaring iwanang aktibo ang isang account nang may kumpiyansa.',
                    ],
                ],
                [
                    'title'  => 'Paano baguhin ang magagawa ng isang role',
                    'steps'  => [
                        'Buksan ang Pangangasiwa → Mga Role ng User at buksan ang role.',
                        'I-on o i-off ang mga switch ng permission. Ang mga switch ay pinangkat ayon sa module at ang bawat isa ay pinapangalanan ang pahinang binubukas nito.',
                        'I-save.',
                    ],
                    'result' => [
                        'Ang bawat opisyal na mayroon ng role ay makakabukas ng mga bagong naka-on na pahina, at mawawalan ng mga naka-off, sa susunod na mag-load sila ng pahina.',
                    ],
                    'notes'  => [
                        'Ang role na Administrator ay ang catalogue mismo: hawak nito ang lahat kahit walang switch na nakasulat sa row nito, at hindi kailanman iniaalok sa opisina ang switch para dito.',
                        'Ang role na hawak pa ng isang opisyal ay hindi maaaring tanggalin — ilipat muna ang mga opisyal sa ibang role.',
                        'Ang pag-off ng permission ay hindi nagtatago ng kasaysayan. Nananatili ang mga rekord; ang pahina lang ang isinasara.',
                    ],
                ],
                [
                    'title'  => 'Paano i-deactivate ang isang opisyal',
                    'steps'  => [
                        'Buksan ang Pangangasiwa → Mga User at hanapin ang account.',
                        'I-switch ito sa inactive.',
                    ],
                    'result' => [
                        'Hindi na makakapag-sign in ang opisyal, at lahat ng naitala nila ay nananatili sa file.',
                    ],
                    'notes'  => [
                        'Tinatanggihan ng pahina ng sign-in ang deactivated na account kahit tama ang password.',
                        'Ang opisyal na nakalimutan lang ang password ay mas mabuting pagsilbihan ng reset flow kaysa sa bagong account.',
                    ],
                ],
            ],
        ],

        'settings' => [
            'title'    => 'Mga Setting',
            'sections' => [
                [
                    'title'  => 'Ano ang saklaw ng Mga Setting',
                    'body'   => 'Lahat ng binabasa ng application bilang configuration sa halip na iniimbak bilang rekord. Ang mga naka-save na setting ay naka-cache at inihahatid nang sariwa saanman — ang pampublikong site, ang portal, bawat napi-print na dokumento at bawat screen na nagpapakita ng sariling pangalan ng asosasyon.',
                    'fields' => [
                        'General'     => 'Ang pangalan, logo, detalye ng kontak at iba pang impormasyong pangpubliko ng asosasyon.',
                        'Collections' => 'Ang mga default ng pagsingil: mga rate ng dues, araw ng pagbabayad, grace, paalala at multa.',
                        'Security'    => 'Lockout sa login, patakaran ng password at expiry.',
                        'Authentication' => 'Kung maaaring magrehistro ang opisina at ang mga residente, at kung agad na tinatanggap ang bagong account.',
                        'Profile'     => 'Ang iyong sariling account: larawan, email address at password.',
                        'Print'       => 'Paano pinamumunuan at pinipirmahan ang bawat napi-print na dokumento.',
                        'Backup'      => 'Kumuha ng database backup at i-download ang nakuha na.',
                    ],
                ],
                [
                    'title'  => 'Paano baguhin ang isang setting',
                    'steps'  => [
                        'Buksan ang pangkat mula sa menu ng Mga Setting.',
                        'Baguhin ang mga halagang ipinapakita ng pahina.',
                        'I-save.',
                    ],
                    'result' => [
                        'Lalabas ang kumpirmasyon, at nakapasok na ang pagbabago sa susunod na pahinang i-load mo — ang bagong letterhead ay umaabot sa pinakasusunod na dokumento, ang bagong pangalan sa susunod na pag-render ng pahina.',
                    ],
                    'notes'  => [
                        'Ang mga halagang nagpapatakbo sa framework — haba ng session, lockout, tuntunin ng password — ay sinusuri kung saan isinulat ang mga ito, kaya ang hindi magagamit na halaga ay tinatanggihan sa halip na i-save.',
                        'Ang letterhead ay rich text, pinapalinis sa pagpasok: nananatili ang pinapayagang formatting, hindi ang mga script.',
                    ],
                ],
                [
                    'title'  => 'Mga setting ng seguridad at ang pinapatakbo nila',
                    'body'   => 'Ang pangkat ng seguridad ay hindi imbakan: ang bawat halaga ay ginagamit ng isang pinangalanang bahagi ng sign-in flow.',
                    'fields' => [
                        'Minimum na haba ng password at mga tuntunin ng karakter' => 'Ipinapatupad saanman tumatanggap ng bagong password: form ng account ng opisyal, parehong reset flow, self-registration at parehong change-password form.',
                        'Expiry ng password' => 'Gaano katagal magagamit ang password bago itabi ang account sa pahina ng profile nito hanggang mabago ito.',
                        'Mga pagtatangka sa login at lockout' => 'Ilan ang tinatanggap na maling pagtatangka at gaano katagal hinihinto ang sign-in.',
                        'Session timeout'    => 'Gaano katagal nabubuhay ang idle session, at kung ang pagsara ng browser ay nagtatapos nito.',
                        'Login enabled'      => 'Kung ang sign-in ay tinatanggap — sa panel at sa portal.',
                    ],
                ],
                [
                    'title'  => 'Mga backup',
                    'steps'  => [
                        'Buksan ang Mga Setting → Backup.',
                        'Kumuha ng backup at hintayin itong matapos.',
                        'I-download ang file kapag iniaalok na.',
                    ],
                    'result' => [
                        'Lalabas ang backup sa listahan kasama ang laki nito at ang oras na kinuha, at nagda-download bilang file na itatago sa labas ng server.',
                    ],
                    'notes'  => [
                        'Kumuha ng isa bago ang pagbabagong hindi ka sigurado, at panatilihin ang kopya sa labas ng server — ang backup sa parehong disk ay hindi backup.',
                    ],
                ],
            ],
        ],

        'by-role' => [
            'title'    => 'Pagtrabaho Ayon sa Role',
            'sections' => [
                [
                    'title'  => 'Paano ipinagkakaloob ang access',
                    'body'   => 'Ang bawat pahina at bawat aksyon sa panel ay isang pinangalanang permission. Ang role ay isang bungkos ng mga permission, at ang account ng opisyal ay may eksaktong isang role. Iyon ang buong modelo ng awtorisasyon — walang nakatagong ikalawang listahan kung sino ang maaaring gumawa ng ano.',
                    'fields' => [
                        'Permission'    => 'Isang switch: "maaaring magdagdag ng resident", "maaaring mag-apruba ng gastos". Ang mga permission ay pinangkat ayon sa module na kinabibilangan.',
                        'Role'          => 'Isang pinangalanang bungkos ng mga permission, ipinagkakaloob sa mga opisyales na gumagawa ng gawaing iyon.',
                        'Account'       => 'Ang sign-in ng isang opisyal, may isang role, na maaaring i-off nang hindi nawawala ang kasaysayan.',
                        'Administrator' => 'Ang role na pinapatakbo ng software. Hawak nito ang buong catalogue at ito ang nag-iisang role na hindi maaaring alisin ng opisina.',
                        'Visitor'       => 'Ang isa pang role na kasama ng software: isang limitado, read-only na panimulang punto para sa guest account.',
                    ],
                ],
                [
                    'title'  => 'Sino ang gumagawa ng ano',
                    'body'   => 'Ang mga role na aktwal na ginagamit ng asosasyon ay sarili nitong itakda: gumawa ng isa kada uri ng trabaho at ipagkaloob lamang ang kailangan ng gawaing iyon. Ito ang mga hugis na mahusay gumagana.',
                    'fields' => [
                        'Administrator'              => 'Lahat: mga account at role ng opisyal, bawat setting, bawat pahina, bawat ulat.',
                        'Finance officer'            => 'Ang mga pahina ng pagsingil at resibo, ang mga pahina ng gastos, ang mga libro, at ang mga ulat ng pananalapi.',
                        'Collection officer'         => 'Pagsingil at resibo lamang: magtaas ng invoice, patakbuhin ang dues, tumanggap ng bayad, mag-isyu ng statement.',
                        'Records / front-desk officer' => 'Ang mga rekord ng homeowner at ang mga pahina ng suporta — pag-profile ng resident, pag-isyu ng account, paghahain at pagpapasya ng request, pagrekord ng paglabag.',
                        'Bookings officer'           => 'Mga amenities, ang mga booking at ang kalendaryo nito, at ang mga request na may kaugnayan sa mga ito.',
                        'Reporting officer'          => 'Mga ulat lamang, basahin at i-export. Kapaki-pakinabang para sa miyembro ng board na kailangan ang mga pigura at wala nang iba.',
                        'Auditor / guest'            => 'Ang role na Visitor sa panimulang punto nito: read-only, at binibigyan ng dagdag lamang kung saan gusto ng asosasyon.',
                    ],
                    'notes'  => [
                        'Ang role ay maaaring may mga permission mula sa ilang lugar; walang nag-oobliga ng isang role kada module.',
                        'Ipagkaloob ang pinakamaliit na hanay na nagpapahintulot sa opisyal na gawin ang trabaho, at dagdagan ito kapag hinihingi ng gawain.',
                    ],
                ],
                [
                    'title'  => 'Ano ang nakikita ng opisyal na walang permission',
                    'body'   => 'Wala. Ang entry sa menu na hindi kayang gamitin ng role ay hindi iginuguhit, kaya ang sidebar ang tapat na listahan ng maaaring buksan ng opisyal na iyon. Hindi rin gumagana ang manu-manong pag-type ng address ng saradong pahina: sumasagot ang panel na para bang hindi umiiral ang pahina sa halip na ipakita itong walang laman.',
                    'steps'  => [
                        'Mag-sign in bilang ang opisyal, o basahin ang role sa Pangangasiwa → Mga User.',
                        'Ihambing ang sidebar sa sinasabing hindi mahanap ng opisyal.',
                        'Kung may nawawalang pahina, buksan ang role at i-on ang permission — iyon ang solusyon, hindi ang pangalawang account.',
                    ],
                    'result' => [
                        'Inililista ng sidebar ng opisyal ang eksaktong mga pahinang binubukas ng kanilang role, at ang manwal na ito ay naglilista ng eksaktong mga tool na maaari nilang basahin.',
                    ],
                ],
                [
                    'title' => 'Iba ang panig ng homeowner',
                    'body'  => 'Ang portal ay walang role. Ang homeowner ay nakikita ang sariling sambahayan at wala nang iba — ang sarili nilang bill, resibo, booking, request at miyembro — dahil pagmamay-ari, hindi permission, ang nagtatakda ng maaari nilang buksan. Huwag hanapin ang setting ng role upang baguhin ang nakikita ng homeowner; ang sagot ay laging ang sariling account ng rekord.',
                ],
            ],
        ],

        'troubleshooting' => [
            'title'    => 'Pag-troubleshoot',
            'sections' => [
                [
                    'title'  => 'Mga problema sa sign-in',
                    'fields' => [
                        '"Hindi tugma sa aming mga rekord ang mga kredensyal na ito."' => 'Tingnan muna ang email address — ito ang nasa account, hindi laging ang gusto ng opisyal. Kung nawala ang password, gamitin ang Nakalimutan ang password sa halip na maghula.',
                        'Tinatanggihan ang sign-in pagkatapos ng ilang pagtatangka' => 'Pansamantalang hinihigpitan ang account. Hintayin ang window, o hilingin sa Administrator na itaas ang pinapayagang pagtatangka sa Mga Setting → Security.',
                        'Hindi dumarating ang code sa email' => 'Humiling ng bagong code, pagkatapos ay tingnan ang email address sa account. Nadedelay rin ang mga code ng cooldown pagkatapos ng paulit-ulit na hiling.',
                        'Paulit-ulit kang ipinapadala ng panel sa iyong profile' => 'Lumampas na ang password sa expiry window. Baguhin ito sa pahina ng profile at bubukas nang normal ang panel.',
                        '"Na-deactivate ang iyong account"' => 'Ang naka-off na account ay hindi makakapag-sign in kahit tama ang password. Hilingin sa Administrator na i-reactivate ito.',
                    ],
                ],
                [
                    'title'  => 'Nawawala ang isang pahina o button',
                    'body'   => 'Iginuguhit lamang ng panel ang maaaring buksan ng role. Ang nawawalang entry sa menu ay permission, hindi sira.',
                    'steps'  => [
                        'Tingnan ang role sa Pangangasiwa → Mga User.',
                        'Buksan ang role at hanapin ang switch para sa pahina o aksyon na nawawala.',
                        'I-on ito at i-save.',
                    ],
                    'result' => [
                        'Lalabas ang entry sa menu sa susunod na pag-load ng pahina ng opisyal.',
                    ],
                    'notes'  => [
                        'Ang button sa loob ng pahina ay maaaring nawawala sa parehong dahilan: ang aksyon ay may sariling permission — iba ang pag-apruba sa pagtingin.',
                    ],
                ],
                [
                    'title'  => 'Hindi naglo-load ang dialog',
                    'body'   => 'Kinukuha ng mga dialog ang kanilang rekord mula sa server habang bumubukas, kaya ang spinner na hindi natatapos ay nangangahulugang tinanggihan o hindi mahanap ng server ang rekord — kadalasan dahil iniwan bukas ang pahina habang nagbago ang rekord sa ilalim nito.',
                    'steps'  => [
                        'Isara ang dialog at i-reload ang pahina.',
                        'Buksan muli ang rekord at ulitin ang aksyon.',
                    ],
                    'result' => [
                        'Bubukas ang dialog kasama ang rekord at ang mga galaw na naaangkop pa rito.',
                    ],
                    'notes'  => [
                        'Sinasadya ang pagtanggi: ang galaw na iniaalok ng lipas nang pahina — pag-apruba ng naaprubahan na, pagbabayad ng nabayaran na — ay tinatanggihan sa halip na ulitin.',
                    ],
                ],
                [
                    'title'  => 'Hindi binibilang ang isang bayad',
                    'body'   => 'Ang resibong inihain mula sa portal ay naghihintay bilang pending: wala itong binabayaran at hindi umaabot sa mga libro hanggang aprubahan ito ng opisina sa pahina ng Mga Bayad. Kaya ang isang sambahayan ay maaaring magpakita ng inihain na bayad habang nakatayo pa ang bill.',
                    'steps'  => [
                        'Buksan ang listahan ng mga bayad at i-filter ang pending na resibo.',
                        'Tingnan ang patunay at reference nito, pagkatapos ay aprubahan ito — o tanggalin upang makapaghain muli ang sambahayan.',
                    ],
                ],
                [
                    'title'  => 'Mukhang mali ang isang balanse',
                    'body'   => 'Walang naka-imbak na balanse, kaya walang matitinag sa hakbang. Ang bawat pigura ay muling kinukwenta mula sa mga resibo at posting sa likod nito, na nagpapaliit sa tanong sa kung ano ang aktwal na naitala.',
                    'fields' => [
                        'Nagpapakita pa rin ng balanse ang bill kahit may ibinigay na pera' => 'Ang resibo ay pending pa, o naitala laban sa ibang bill.',
                        'Mukhang mas mababa ang kita kaysa sa koleksyon' => 'Nakikilala ang kita kapag ipinost ng accounting ang isang bayad, hindi kapag itinaas ang bill — tingnan ang journal para sa mga naka-post na bayad.',
                        'Hindi lumalabas ang gastos sa mga libro' => 'Ipinopost ang gastos kapag ipinost ito ng accounting, hindi kapag inaprubahan. Ang hindi pa naka-post na gastos ay wala pang journal entry.',
                        'Mukhang kulang ang isang buwan sa ulat' => 'Tingnan kung bukas pa ang buwan, at kung may na-void o na-reverse pagkatapos.',
                    ],
                ],
                [
                    'title'  => 'Mali ang napi-print na dokumento',
                    'fields' => [
                        'Blangko ang isang field' => 'Ang dokumento ay ipi-print mula sa mga rekord: ang blangkong linya ay nangangahulugang blangko ang rekord. Kumpletuhin ang profile ng resident o ang mga setting at i-print muli.',
                        'Lipas na ang letterhead' => 'Baguhin ito sa Mga Setting → Print. Gagamitin ito ng pinakasusunod na dokumento.',
                        'Humihinto ang mahabang listahan sa gitna' => 'Ang dokumento ay hinahati-hati upang manatiling napi-print ang isang PDF. Hatiin ang trabaho ayon sa address o hanay at i-print ang bawat bahagi.',
                        'Mali ang laki ng papel' => 'Itakda ito sa Mga Setting → Print, o gamitin ang preview upang tingnan ang pahina bago mag-print.',
                    ],
                ],
                [
                    'title'  => 'Saan makakakuha ng tulong',
                    'body'   => 'Magsimula sa opisina: ang Administrator ng asosasyon ang may hawak ng mga setting ng account at role at makakapagbalik ng access, at ang sariling detalye ng kontak ng asosasyon ay nakalathala sa pampublikong site. Kung ang isang rekord ay mukhang mali sa halip na nawawala, ang kabanata para sa module na iyon ay nagsasaad ng tuntuning sinusunod nito, at tinutukoy ng Talasalitaan ang mga termino nito.',
                    'notes'  => [
                        'Banggitin ang reference ng rekord — numero ng invoice, booking, request — kapag humihingi ng tulong: maaari itong direktang hanapin.',
                        'Panatilihin ang backup bago ang malaking pagbabagong hindi ka sigurado.',
                    ],
                ],
            ],
        ],

        'faq' => [
            'title'    => 'FAQ at Mga Tip',
            'sections' => [
                [
                    'title'  => 'Mga karaniwang tanong',
                    'fields' => [
                        'Maaari bang mamahala ang isang tao ng dalawang role?' => 'Hindi — ang account ay may eksaktong isa. Gumawa ng role na nagdadala ng dalawang hanay ng permission sa halip.',
                        'Maaari ko bang i-undo ang tinanggal na rekord?' => 'Hindi sa pamamagitan ng pagbabalik nito. Ang mga rekord na maaaring ibalik ay kinakansela o vine-void sa halip na tinatanggal para sa eksaktong dahilang ito; ang anumang nakolekta na ay ibinabalik ng correcting entry.',
                        'Bakit nakikita ko ang bill ngunit hindi makapag-post ng bayad dito?' => 'Ang bill ay may resibo nang naghihintay ng apruba. Aprubahan o tanggihan muna ang isang iyon — hindi kailanman kinukuha nang dalawang beses ang parehong pera.',
                        'Bakit kailangan kong aprubahan ang bayad na inihain ng homeowner?' => 'Ang portal ay salita ng sambahayan; ang mga libro ay sa opisina. Ang pag-apruba ang lugar kung saan inaako ng opisina ang responsibilidad sa pera.',
                        'Bakit hindi available sa kanila ang dokumento ng homeowner?' => 'Dapat aprubahan ang request at bayaran ang invoice na itinaas para dito, sa loob ng window at badyet ng download na itinakda ng apruba.',
                        'Ano ang pagkakaiba ng bill at invoice?' => 'Ang invoice ay isang uri ng bill. Ang buwanang dues at bayad na booking ay mga bill ng ibang uri, at lahat ng ito ay nasa isang ledger.',
                        'Bakit hindi ako na-sign out kapag isinara ang browser?' => 'Ang session ay humihigit sa tab hanggang mag-time out. Mag-sign out nang maayos sa shared computer; ang timeout mismo ay itinakda sa Mga Setting → Security.',
                        'Maaari bang magkaroon ng dalawang sambahayan ang isang property?' => 'Hindi. Isang sambahayan kada unit, at isang unit kada sambahayan.',
                        'Bakit walang laman ang menu na nakikita ko?' => 'Ang listahan ay na-filter, o na-clear ang mga rekord — tingnan ang toolbar bago magpasya na may nawawala.',
                        'Paano ko malalaman ang ginawa ng isang opisyal?' => 'Itinatala ng activity log ang mga aksyong ginawa sa system, at itinatala ng mga libro ang mismong mga paggalaw ng pera.',
                    ],
                ],
                [
                    'title'  => 'Mga gawi na nakakatipid ng oras',
                    'body'   => 'Isang maikling listahan, sulit basahin nang isang beses.',
                    'notes'  => [
                        'Maghanap sa sidebar bago maghanap sa mga menu: nahahanap nito ang anumang pahinang mabubuksan mo.',
                        'Tsekan ang mga row bago sumapit sa bulk action — lalabas lamang ang selection bar kapag may napili.',
                        'Basahin ang menu ng Aksyon ng row sa halip na maghula: inililista nito ang eksaktong mga galaw na pinapayagan pa ng rekord.',
                        'I-export ang view na na-filter mo; ang file ay eksaktong ang mga row sa screen.',
                        'Tingnan ang ulat bago magsara ng panahon — ang saradong buwan ay hindi na tumatanggap ng posting.',
                        'I-print ang voucher kasama ang posting: ito ang rekord na maaari mong ibigay sa auditor.',
                        'Gamitin ang sariling search box ng manwal (sa sidebar nito) upang tumalon sa kabanata para sa pahinang kinalalagyan mo.',
                    ],
                ],
            ],
        ],

        'glossary' => [
            'title'    => 'Talasalitaan',
            'sections' => [
                [
                    'title'  => 'Mga terminong ginamit sa manwal na ito',
                    'fields' => [
                        'Account'            => 'Ang sign-in ng opisyal sa panel, o ang login ng homeowner sa portal. Dalawang magkaibang uri ng account at hindi kailanman naghahalo.',
                        'Advance deposit'    => 'Kredito na hawak ng asosasyon para sa account ng sambahayan at ibinabawas laban sa mga susunod na singil.',
                        'Amenity'            => 'Isang pinagsasaluhang pasilidad na maaaring ireserba, may rate, lingguhang iskedyul at opsyonal na deposit.',
                        'Bill'               => 'Isang singil sa ledger — dues, invoice o bayad na booking. May balanse ito hanggang mabayaran ng mga resibo nito.',
                        'Booking'            => 'Isang reserbasyon ng amenity para sa isang petsa at hanay ng oras.',
                        'Chart of accounts'  => 'Ang listahan ng mga account na pinagpapatungan ng mga libro, bawat isa may uri at code.',
                        'Reklamo (Complaint)' => 'Isang alalahanin na inihain ng homeowner, sinasagot ng opisina.',
                        'Deposit'            => 'Perang hawak laban sa isang booking at ibinabalik o iforfeit pagkatapos.',
                        'Talahanayan ng dues' => 'Ang mga bill ng buwanang dues, inayos para sa pagsusuri.',
                        'Sambahayan'         => 'Ang mga taong nakatira sa isang unit: ang sambahayan ng account at ang mga miyembro nito.',
                        'Invoice'            => 'Isang bill na itinataas ng opisina nang manu-mano, kumpara sa dues o booking.',
                        'Journal entry'      => 'Isang balanseng posting sa mga libro — ang rekord ng paggalaw ng pera.',
                        'Ledger'             => 'Ang kabuuan ng mga journal entry; ang mga libro.',
                        'Letterhead'         => 'Ang heading, footer at mga pinipirmahan na dala ng bawat napi-print na dokumento. Nasa Mga Setting → Print.',
                        'Buwanang dues'      => 'Ang paulit-ulit na singil na itinataas kada unit para sa isang buwan.',
                        'Bayad (Payment)'    => 'Isang resibo laban sa bill. Kinuha sa opisina, agad itong naitala; inihain mula sa portal, naghihintay bilang pending.',
                        'Period close'       => 'Pagyeyelo ng isang buwan upang walang makapag-post pa dito.',
                        'Pahintulot (Permission)' => 'Isang switch sa isang role, pinapangalanan ang isang pahina o aksyon.',
                        'Portal'             => 'Ang kalahati ng system na para sa homeowner, sa /portal.',
                        'Produkto'           => 'Isang sisingiling bagay na pinagbubuhatan ng invoice o booking, pinangkat sa ilalim ng kategorya.',
                        'Property'           => 'Isang lot o unit sa subdivision, nakaugnay sa isang address.',
                        'Resibo (Receipt)'   => 'Ang naitalang pera na kinakatawan ng isang bayad.',
                        'Resident'           => 'Isang taong na-profile ng opisina. Ang account ng homeowner ay naka-link sa isa lang.',
                        'Role'               => 'Isang pinangalanang bungkos ng mga permission, hawak ng account ng opisyal.',
                        'Statement of Account' => 'Ang mga bukas na bill ng sambahayan, nakalistang sama-sama at ipinadala bilang pahina o sa email.',
                        'Transfer'           => 'Pera na inilipat sa pagitan ng dalawang account nang walang bill o gastos sa likod.',
                        'Paglabag (Violation)' => 'Isang naitalang paglabag sa mga tuntunin ng asosasyon, may napi-print na notice.',
                    ],
                ],
            ],
        ],

    ],
];
