<?php

/*
|--------------------------------------------------------------------------
| Manwal ng Gumagamit — Portal ng Homeowner
|--------------------------------------------------------------------------
|
| Ang kopya ng portal, naka-key sa mga paksang pinapangalanan ng catalogue sa
| `config/manual.php` sa ilalim ng `portal`. Base sa pagmamay-ari ang portal,
| kaya iniaalok ang bawat paksa sa anumang naka-sign in na homeowner at walang
| nagdedeklara ng abilities. Pareho ang hugis ng paksa sa panig ng admin: isang
| `title` at ang mga `sections` nito.
|
*/

return [

    'title' => 'Manwal ng Gumagamit',
    'intro' => 'Paano gamitin ang iyong portal ng homeowner, pahina sa bawat pahina.',

    'groups' => [
        'getting_started' => 'PAGSISIMULA',
        'services'        => 'MGA SERBISYO',
        'finance'         => 'PANANALAPI',
        'my_account'      => 'AKING ACCOUNT',
        'help'            => 'TULONG',
    ],

    'topics' => [

        'introduction' => [
            'title'    => 'Panimula',
            'sections' => [
                [
                    'title' => 'Para saan ang portal',
                    'body'  => 'Ang portal ay ang panig ng iyong sambahayan sa mga rekord ng asosasyon. Binabasa mo ang nasa file ng opisina, binabayaran ang iyong mga bill, nagrereserba ng mga amenities, at naghahain at sumusubaybay sa anumang hinihiling mo sa asosasyon — nang hindi bumibisita o tumatawag sa opisina.',
                ],
                [
                    'title' => 'Para kanino ito',
                    'body'  => 'Ang bawat sambahayan ay may isang portal account, ibinigay ng opisina sa isang resident ng unit at naka-link sa rekord ng resident na iyon. Nakikita mo ang sarili mong sambahayan at wala nang iba: ang iyong mga bill, resibo, booking at paghahain.',
                ],
                [
                    'title'  => 'Paano nakaayos ang portal',
                    'fields' => [
                        'Sidebar'      => 'Bawat pahina na mayroon ka, pinangkat ayon sa lugar: mga serbisyo, pananalapi at ang iyong sariling account. Ang search box sa tuktok nito ay nakakahanap ng pahina sa pamamagitan ng pangalan.',
                        'Navbar'       => 'Ang bar sa itaas: ang bell ay bumibilang ng iyong mga notification, at ang iyong pangalan ay nagbubukas ng iyong profile at ng sign-out.',
                        'Dashboard'    => 'Kung saan ka dumarating pagkatapos mag-sign in: ang iyong mga bilang, ang iyong paghahain at ang iyong taon sa isang sulyap.',
                        'Content area' => 'Ang pahinang binuksan mo, may pamagat.',
                    ],
                ],
                [
                    'title'  => 'Mga pangunahing termino',
                    'fields' => [
                        'Statement of Account' => 'Ang listahan ng mga bill na kailangan pang bayaran ng iyong sambahayan.',
                        'Bayad (Payment)'      => 'Isang resibong inihain mo laban sa isang bill. Binibilang ito kapag inaprubahan ng opisina.',
                        'Booking'              => 'Isang reserbasyong inihain mo para sa isang amenity sa isang petsa at oras.',
                        'Request'              => 'Ang aplikasyon mo para sa isang dokumento o serbisyo mula sa opisina.',
                        'Resident'             => 'Ang taong na-profile ng opisina — ikaw. Ang iyong account ay naka-link sa rekord na iyon.',
                    ],
                    'notes'  => [
                        'Mas maraming termino ang tinutukoy ng sariling manwal ng opisina; magtanong sa opisina kung may hindi malinaw sa isang pahina.',
                    ],
                ],
            ],
        ],

        'getting-started' => [
            'title'    => 'Pagsisimula',
            'sections' => [
                [
                    'title'  => 'Pagbukas ng portal',
                    'steps'  => [
                        'I-type ang web address ng asosasyon sa iyong browser at idagdag ang /portal, pagkatapos pindutin ang Enter.',
                        'Kung hindi ka pa naka-sign in, bubukas ang pahina ng sign-in.',
                    ],
                    'result' => [
                        'Lalabas ang pahina ng sign-in, na humihingi ng iyong email address at password.',
                    ],
                    'notes'  => [
                        'Ang opisina ang magbibigay sa iyo ng address; pareho ito ng site ng pampublikong pahina ng asosasyon.',
                    ],
                ],
                [
                    'title'         => 'Paano mag-sign in',
                    'prerequisites' => [
                        'Ang account na ibinigay ng opisina sa iyong sambahayan.',
                    ],
                    'steps'  => [
                        'Buksan ang pahina ng sign-in.',
                        'Ilagay ang email address at password sa iyong account.',
                        'Piliin ang Sign in.',
                    ],
                    'result' => [
                        'Bubukas ang iyong dashboard, na pinamumunuan ng iyong pangalan sa navbar.',
                    ],
                    'notes'  => [
                        'Walang sign-up form: ang mga account ay ibinibigay ng opisina, isa kada sambahayan.',
                        'Kung masyadong maraming pagtatangka ang bumagsak nang sunod-sunod, hihinto sandali ang sign-in; maghintay at subukang muli.',
                    ],
                ],
                [
                    'title'  => 'Nakalimutang password',
                    'steps'  => [
                        'Piliin ang Nakalimutan ang password? sa pahina ng sign-in.',
                        'Ilagay ang email address sa iyong account at isumite.',
                        'Buksan ang email at sundan ang reset link.',
                        'Pumili ng bagong password na tumutugon sa mga tuntuning ipinapakita, at kumpirmahin ito.',
                    ],
                    'notes'  => [
                        'Nag-e-expire ang link; humiling ng bago kung nag-expire na.',
                        'Kung hindi dumarating ang reset email, matitingnan ng opisina kung anong address ang dala ng iyong account.',
                    ],
                ],
                [
                    'title'  => 'Mabilis na paglibot sa iyong screen',
                    'fields' => [
                        'Dashboard'            => 'Ang iyong mga bilang, ang iyong paghahain ayon sa status, at ang iyong booking at bayad para sa taon.',
                        'Aking mga Booking'    => 'Ang mga amenity na iyong nireserba at kung saan nakatayo ang bawat reserbasyon.',
                        'Mga Kahilingan'       => 'Ang hiniling mo sa opisina, at ang nangyari dito.',
                        'Mga Reklamo'          => 'Mga alalahanin na iyong inihain, kasama ang sagot ng opisina.',
                        'Mga Paglabag'         => 'Anumang paglabag sa tuntuning naitala laban sa iyong sambahayan.',
                        'Statement of Account' => 'Ang mga bill na kailangan pang bayaran, at paano sila bayaran.',
                        'Aking mga Bayad'      => 'Ang mga resibong inihain mo at kung saan nakatayo ang bawat isa.',
                        'Profile'              => 'Ang iyong larawan, email address at password.',
                        'Personal na Impormasyon' => 'Ang mga detalye ng resident na inaayos ng opisina para sa iyo.',
                        'Sambahayan'           => 'Ang iyong unit at ang mga taong nakatira dito.',
                    ],
                ],
                [
                    'title' => 'Ano ang binabago mo at ano ang binabago ng opisina',
                    'body'  => 'Sa iyo ang sarili mong detalye: ang iyong larawan, password, impormasyon ng kontak at mga miyembro ng sambahayan. Sa opisina ang mga rekord na nagtatakda ng pera at mga tuntunin — mga bill, resibo, booking, request at paglabag. Maaari kang maghain at sumubaybay sa lahat ng iyon, ngunit ang opisina lamang ang nag-aapruba, nagpepresyo at nagbabayad sa mga ito.',
                    'notes' => [
                        'Walang inihain mo ang nagiging pera hanggang aprubahan ito ng opisina: ang bayad na isinumite mo ay naghihintay, at nakatayo pa rin ang bill hanggang maaprubahan ito.',
                    ],
                ],
                [
                    'title' => 'Pag-sign out',
                    'body'  => 'Buksan ang iyong pangalan sa kanan ng navbar at piliin ang Sign out. Sa shared computer, isara rin ang browser.',
                ],
            ],
        ],

        'dashboard' => [
            'title'    => 'Dashboard',
            'sections' => [
                [
                    'title'  => 'Ang iyong mga bilang',
                    'body'   => 'Ang apat na kard sa itaas ay bumibilang sa inihain at nireserba ng iyong sambahayan. Ang bawat kard ay nagli-link sa pahina sa likod ng pigura.',
                    'fields' => [
                        'Mga Kahilingan' => 'Ang hiniling mo sa opisina.',
                        'Mga Reklamo'    => 'Mga alalahanin na iyong inihain.',
                        'Mga Paglabag'   => 'Mga paglabag sa tuntuning naitala laban sa iyong sambahayan.',
                        'Mga Booking'    => 'Mga reserbasyon ng amenity na iyong ginawa.',
                    ],
                ],
                [
                    'title'  => 'Aking paghahain',
                    'body'   => 'Isang chart ng iyong paghahain ayon sa status at isang maikling listahan ng iyong pinakabago, upang makita mo sa isang sulyap kung may naghihintay pa sa opisina.',
                    'steps'  => [
                        'Buksan ang isang paghahain mula sa listahan upang basahin ito nang buo.',
                        'Gamitin ang link ng kard upang makita ang lahat ng inihain mo.',
                    ],
                ],
                [
                    'title' => 'Taon sa isang sulyap',
                    'body'  => 'Ang iyong booking at bayad para sa taon, magkatabi. Kasama ang resibong naghihintay pa sa opisina; sinasabi ng pahina ng Aking mga Bayad kung saan nakatayo ang bawat isa.',
                ],
            ],
        ],

        'bookings' => [
            'title'    => 'Aking mga Booking',
            'sections' => [
                [
                    'title'         => 'Paano mag-book ng amenity',
                    'prerequisites' => [
                        'Dapat iniaalok ang amenity sa mga homeowner — ang opisina ang nagpapasya niyan.',
                    ],
                    'steps'  => [
                        'Buksan ang Aking mga Booking at piliin ang Humiling ng booking.',
                        'Piliin ang amenity na gusto mo, pagkatapos ang petsa at ang mga oras.',
                        'Tingnan ang bayad at anumang deposit na ipinapakita ng form, at isumite.',
                    ],
                    'result' => [
                        'Lalabas ang booking sa iyong listahan bilang Pending.',
                        'Nakikita ng opisina ang parehong reserbasyon at kinukumpirma ito bago maging sa iyo ang slot.',
                    ],
                    'notes'  => [
                        'Ang libreng amenity ay hindi kailanman humihingi ng bayad. Ang booking na may bayad ay nagtataas ng invoice sa iyong Statement of Account kapag inaprubahan ito ng opisina.',
                        'Tingnan ang iskedyul ng amenity bago pumili ng mga oras: ang araw na sarado ang pasilidad ay hindi maaaring i-book.',
                    ],
                ],
                [
                    'title'  => 'Paano mag-withdraw ng booking',
                    'body'   => 'Maaari mong i-withdraw ang booking habang hindi pa ito inaaksyunan ng opisina. Kapag nakumpirma na, ang opisina lamang ang makakabago nito.',
                    'steps'  => [
                        'Buksan ang Aking mga Booking at hanapin ang reserbasyon.',
                        'Piliin ang I-withdraw at kumpirmahin.',
                    ],
                    'result' => [
                        'Mababasa ang booking bilang nakansela at libre na muli ang slot.',
                    ],
                ],
                [
                    'title'  => 'Pagbasa ng status',
                    'fields' => [
                        'Pending'   => 'Hindi pa ito inaaksyunan ng opisina. Maaari mo pang i-withdraw.',
                        'Nakumpirma' => 'Sa iyo na ang slot. Ang anumang bayad ay sisingilin sa iyong sambahayan.',
                        'Tapos na'  => 'Nagamit na ang reserbasyon.',
                        'Nakansela' => 'Hindi na ito nakatayo.',
                    ],
                ],
            ],
        ],

        'requests' => [
            'title'    => 'Mga Kahilingan',
            'sections' => [
                [
                    'title'  => 'Paano maghain ng request',
                    'body'   => 'Hilingin sa opisina ang isa sa mga dokumento o serbisyo ng asosasyon. Ang form ay humihingi lamang ng kailangan ng dokumentong pinili mo.',
                    'steps'  => [
                        'Buksan ang Mga Kahilingan at piliin ang Maghain ng request.',
                        'Piliin ang hinihiling mo.',
                        'Punan ang mga field na lalabas — ang ilang request ay walang hinihingi maliban sa rekord na hawak na ng opisina.',
                        'Isumite ito.',
                    ],
                    'result' => [
                        'Lalabas ang request sa iyong listahan bilang Pending, at nakikita ito ng opisina sa parehong estado.',
                    ],
                    'notes'  => [
                        'Ang mga form na buo mula sa iyong profile ay walang hinihingi: panatilihing napapanahon ang iyong Personal na Impormasyon upang tama ang pag-print ng mga ito.',
                        'Maaari mong basahin ang status ng isang request anumang oras mula sa pahinang ito.',
                    ],
                ],
                [
                    'title'  => 'Pagsubaybay at ang iyong dokumento',
                    'fields' => [
                        'Pending'           => 'Hindi pa nagpapasya ang opisina.',
                        'Aprubado'          => 'Inisyu ng opisina ang dokumento. Ang anumang invoice na itinaas para dito ay lalabas sa iyong Statement of Account.',
                        'Tinanggihan'       => 'Hindi ito ipinagkaloob ng opisina, at sinabi kung bakit.',
                        'May naghihintay na bill' => 'Ang isang dokumento ay hinaharang ng hindi pa bayad na invoice: bayaran ang bill at bubukas ang download.',
                        'Download'          => 'Available kapag aprubado ang request at bayad na ang invoice nito, habang bukas ang window at may natitira pang download.',
                    ],
                    'notes'  => [
                        'Kung hinaharang ang download, sinasabi ng kard kung alin sa mga kondisyon ang hindi natugunan.',
                    ],
                ],
            ],
        ],

        'complaints' => [
            'title'    => 'Mga Reklamo',
            'sections' => [
                [
                    'title'  => 'Paano maghain ng reklamo',
                    'steps'  => [
                        'Buksan ang Mga Reklamo at piliin ang Maghain ng reklamo.',
                        'Piliin ang kategoryang angkop at ilarawan ang nangyari.',
                        'Isumite ito.',
                    ],
                    'result' => [
                        'Lalabas ang reklamo sa iyong listahan bilang inihain, kasama ang kategorya nito.',
                    ],
                ],
                [
                    'title' => 'Pagsubaybay dito',
                    'body'  => 'Bawat reklamong inihain mo ay nakalista kasama ang status nito at anumang puna na isinulat ng opisina. Buksan ang isa upang basahin ang sagot ng opisina; wala nang ibang kailangan mula sa iyo.',
                ],
            ],
        ],

        'violations' => [
            'title'    => 'Mga Paglabag',
            'sections' => [
                [
                    'title' => 'Pagbasa ng paglabag',
                    'body'  => 'Ang paglabag ay isang paglabag sa tuntuning itinala ng opisina laban sa iyong sambahayan. Ipinapakita ng bawat entry kung ano ito, kailan nangyari at anumang puna. Buksan ang isa upang basahin ito nang buo.',
                ],
                [
                    'title' => 'Pag-acknowledge',
                    'body'  => 'I-acknowledge ang paglabag kapag nabasa mo na ito. Ang pag-acknowledge ay resibo, hindi pag-amin: sinasabi nito sa opisina na nakita mo ang notice.',
                ],
            ],
        ],

        'statement-of-account' => [
            'title'    => 'Statement of Account',
            'sections' => [
                [
                    'title'  => 'Ang iyong bukas na bill',
                    'body'   => 'Inililista ng statement ang mga bill na kailangan pang bayaran ng iyong sambahayan — dues at isahang singil nang sama-sama — kasama ang mga halaga at petsa ng pagbabayad.',
                    'fields' => [
                        'Reference' => 'Ang sariling numero ng bill. Banggitin ito kapag nagtatanong sa opisina.',
                        'Uri'       => 'Kung saan nanggaling ang singil: buwanang dues, invoice, o bayad na booking.',
                        'Halaga'    => 'Ang natitirang utang dito.',
                        'Petsa ng pagbabayad' => 'Ang araw ng pagbabayad. Ang dues ay may dalang araw na itinakda ng opisina.',
                        'Balanse'   => 'Ang natitira pagkatapos ng bawat resibong naitala laban sa bill.',
                    ],
                ],
                [
                    'title'         => 'Paano magbayad ng bill',
                    'prerequisites' => [
                        'Ang patunay ng bayad — ang deposit slip, resibo ng transfer o counter slip.',
                    ],
                    'steps'  => [
                        'Buksan ang Statement of Account at hanapin ang bill na binabayaran mo.',
                        'Piliin ang Bayaran sa tabi nito.',
                        'Tingnan ang halaga, pagkatapos ay ikabit ang patunay ng bayad at ibigay ang reference nito.',
                        'Isumite.',
                    ],
                    'result' => [
                        'Hindi na nag-aalok ng Bayaran ang bill at nagpapakita ng waiting notice sa halip.',
                        'Lalabas ang bayad sa Aking mga Bayad bilang Pending hanggang aprubahan ito ng opisina.',
                    ],
                    'notes'  => [
                        'Ang bill na may naghihintay nang isa sa iyong mga bayad ay hindi na nag-aalok ng Bayaran, kaya hindi kailanman binabayaran nang dalawang beses ang parehong bill.',
                        'Wala pang nababago sa pagsumite: nababayaran ang bill kapag inaprubahan ng opisina ang resibo.',
                    ],
                ],
                [
                    'title' => 'Kapag naitala ito ng opisina',
                    'body'  => 'Sinusuri ng opisina ang iyong patunay laban sa halaga at inaprubahan ang resibo. Pagkatapos lang nababayaran ang bill at nabibilang ang bayad. Kung may mali sa inihain mo, tinatanggihan ito ng opisina at muling nag-aalok ng Bayaran ang bill upang maihain mo nang tama.',
                ],
            ],
        ],

        'payments' => [
            'title'    => 'Aking mga Bayad',
            'sections' => [
                [
                    'title' => 'Pagbasa ng iyong mga resibo',
                    'body'  => 'Bawat bayad na inihain mo, kasama ang halaga, ang reference na ibinigay mo at ang status nito. Ang bawat row ay tumutukoy sa bill na binabayaran nito.',
                ],
                [
                    'title'  => 'Mga status',
                    'fields' => [
                        'Pending'   => 'Hindi pa ito inaprubahan ng opisina. Hindi nababayaran ang bill sa pansamantala.',
                        'Naitala'   => 'Inaprubahan ito ng opisina. Binibilang ito laban sa bill at lumalabas sa mga libro ng asosasyon.',
                    ],
                    'notes'  => [
                        'Lumalabas din dito ang bayad na itinala ng opisina para sa iyo, kaya ang pahina ang buong kasaysayan ng mga resibo ng iyong sambahayan.',
                    ],
                ],
            ],
        ],

        'profile' => [
            'title'    => 'Profile',
            'sections' => [
                [
                    'title'  => 'Larawan at email',
                    'steps'  => [
                        'Buksan ang Profile.',
                        'Pumili ng larawan, o baguhin ang email address na sinusulatan ng opisina.',
                        'I-save.',
                    ],
                    'result' => [
                        'Lalabas ang larawan sa tabi ng iyong pangalan sa navbar at sa anumang pahinang nagpapakita ng iyong sambahayan.',
                    ],
                    'notes'  => [
                        'Gumamit ng email address na talagang binabasa mo: dito nagpupunta ang mga password reset at notice ng opisina.',
                    ],
                ],
                [
                    'title'  => 'Password',
                    'steps'  => [
                        'Buksan ang Profile.',
                        'Ilagay ang iyong kasalukuyang password, pagkatapos ang bago nang dalawang beses.',
                        'I-save.',
                    ],
                    'notes'  => [
                        'Ang mga tuntuning dapat tugunan ng bagong password ay nakasaad sa form. Kung nag-e-expire ang mga password sa opisina, ang pagbago sa oras ay nagpapanatili sa iyo na naka-sign in.',
                    ],
                ],
            ],
        ],

        'personal-information' => [
            'title'    => 'Personal na Impormasyon',
            'sections' => [
                [
                    'title'  => 'Pagpapanatili nitong napapanahon',
                    'body'   => 'Inaayos ng opisina ang iyong rekord ng resident — ang pangalan, detalye ng kontak at ang natitirang profile. Suriin ito dito at iwasto ang anumang nagbago.',
                    'steps'  => [
                        'Buksan ang Personal na Impormasyon.',
                        'I-update ang mga field na nagbago.',
                        'I-save.',
                    ],
                    'result' => [
                        'Agad na naia-update ang rekord, at pareho ang binabasa ng opisina.',
                    ],
                    'notes'  => [
                        'Iniisyu ng opisina ang mga dokumento at notice mula sa rekord na ito: ang blangkong field ay blangko ang print, kaya punan ang kaya mo.',
                        'Ang ilang detalye ay sa opisina at hindi sa iyo; magtanong sa kanila kung may mukhang mali at hindi ma-edit dito.',
                    ],
                ],
            ],
        ],

        'household' => [
            'title'    => 'Sambahayan',
            'sections' => [
                [
                    'title'  => 'Ang iyong unit at mga miyembro',
                    'body'   => 'Ipinapakita ng pahinang ito ang property na okupado ng iyong sambahayan at ang mga miyembrong nasa file ng opisina para dito.',
                    'fields' => [
                        'Unit'     => 'Ang lot o unit na okupado ng iyong sambahayan, ayon sa naitatala ng opisina.',
                        'Mga Miyembro' => 'Ang mga taong nakatira sa unit, bawat isa may sariling detalye.',
                    ],
                ],
                [
                    'title'  => 'Paano pamahalaan ang mga miyembro',
                    'steps'  => [
                        'Buksan ang Sambahayan at piliin ang Pamahalaan ang mga miyembro.',
                        'Magdagdag ng miyembro sa pamamagitan ng paglalagay ng detalye ng tao.',
                        'Alisin ang miyembrong lumipat na, at kumpirmahin ang pag-alis kapag tinanong.',
                    ],
                    'result' => [
                        'Agad na naia-update ang listahan ng miyembro, at pareho ang binabasa ng opisina.',
                    ],
                    'notes'  => [
                        'Ang pag-alis ay humihingi ng kumpirmasyon at nagkakabisa pagkatapos lang.',
                        'Ang iyong unit at account ay rekord ng opisina: kung mali ang property na ipinapakita, hilingin sa kanila na iwasto ito sa halip na magdagdag ng sarili mong sambahayan.',
                    ],
                ],
            ],
        ],

        'troubleshooting' => [
            'title'    => 'Pag-troubleshoot',
            'sections' => [
                [
                    'title'  => 'Pag-sign in',
                    'fields' => [
                        'Tinatanggihan ng sign-in ang aking password' => 'Tingnan muna ang email address, pagkatapos ay gamitin ang Nakalimutan ang password. Pagkatapos ng ilang maling pagtatangka, hihinto sandali ang sign-in.',
                        'Hindi ko natanggap ang reset email'   => 'Tingnan ang spam folder, pagkatapos ay hilingin sa opisina na kumpirmahin kung anong address ang dala ng iyong account.',
                        'Sinasabi ng portal na hindi aktibo ang aking account' => 'In-off ng opisina ang account. Hilingin sa kanila na i-reactivate ito.',
                        'Walang sign-up form' => 'Ang mga account ay ibinibigay ng opisina, isa kada sambahayan. Hilingin sa kanila na mag-isyu ng isa.',
                    ],
                ],
                [
                    'title'  => 'Mga bill at bayad',
                    'fields' => [
                        'Pending pa rin ang aking bayad' => 'Hindi pa ito inaprubahan ng opisina. Nakatayo pa ang bill hanggang gawin nila ito.',
                        'Nagpapakita pa rin ng balanse ang bill pagkatapos kong magbayad' => 'Ang iyong resibo ay naghihintay ng apruba, o inihain ito laban sa ibang bill.',
                        'Nawawala ang Pay button sa isang bill' => 'May bayad mo nang naghihintay sa bill na iyon. Hintayin ang aksyon ng opisina dito.',
                        'Mali ang ikinabit kong patunay' => 'Hilingin sa opisina na tanggihan ang bayad; muling mag-aalok ng Bayaran ang bill kapag ginawa nila ito.',
                    ],
                ],
                [
                    'title'  => 'Mga booking at dokumento',
                    'fields' => [
                        'Pending pa rin ang aking booking' => 'Kinukumpirma ng opisina ang mga reserbasyon. Walang naka-book hanggang gawin nila ito.',
                        'Hindi ako makapag-withdraw ng booking' => 'Nakumpirma na ito. Hilingin sa opisina na baguhin ito.',
                        'Hindi nagda-download ang aking dokumento' => 'Dapat aprubahan ang request at bayaran ang invoice na itinaas para dito, sa loob ng window ng download. Sinasabi ng kard kung aling kondisyon ang hindi natugunan.',
                        'Tinanggihan ang isang request' => 'Nagtalá ng dahilan ang opisina. Buksan ang request upang basahin ito, at maghain muli kung hindi na naaangkop ang dahilan.',
                    ],
                ],
                [
                    'title'  => 'Mga pahina at detalye',
                    'fields' => [
                        'Nawawala ang isang pahinang inaasahan ko' => 'Bawat pahina ng iyong account ay nasa sidebar. Kung may nawawala, magtanong sa opisina — walang setting na mababago ang portal.',
                        'Mali ang mga miyembro ng aking sambahayan' => 'Buksan ang Sambahayan at pamahalaan ang iyong mga miyembro. Ang unit mismo ay rekord ng opisina.',
                        'Lipas na ang aking larawan o email' => 'Baguhin ito sa Profile.',
                        'Mukhang mali ang isang pigura' => 'Magtanong sa opisina, ibanggit ang reference sa rekord — numero ng bill, booking o request. Pareho ang rekord na binabasa ng opisina at ng iyo.',
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
                        'Paano ko malalaman ang utang ko?' => 'Inililista ng Statement of Account ang bawat bill na kailangan pang bayaran ng iyong sambahayan, dues at singil nang sama-sama.',
                        'Maaari ba akong magbayad ng cash?' => 'Oo — ibigay ito sa opisina at itatala nila ang resibo, na lalabas pagkatapos sa Aking mga Bayad.',
                        'Bakit kailangan ng apruba ang bayad ko?' => 'Ang portal ay salita mo; ang mga libro ay sa opisina. Ang pag-apruba ang lugar kung saan inaako ng opisina ang responsibilidad sa pera.',
                        'Maaari ba akong mag-book para sa taong wala sa sambahayan?' => 'Ang mga reserbasyon ay pagmamay-ari ng account ng sambahayan. Magtanong sa opisina tungkol sa anumang hindi karaniwan.',
                        'Maaari bang magkaroon ng higit sa isang login ang aking sambahayan?' => 'Isang account kada sambahayan, naka-link sa isang resident. Ang pangalawang resident ng unit ay idinadagdag bilang miyembro sa halip.',
                        'Gaano katagal ang isang request?' => 'Ang opisina ang nagpapasya. Nananatiling Pending ang request hanggang gawin nila ito, at mabuting tingnan ang status anumang oras.',
                        'Saan ko nakikita ang mga notice?' => 'Binibilang ito ng bell sa navbar, at lumalabas ang mga anunsyo ng asosasyon sa pampublikong site.',
                        'Maaari ko bang tanggalin ang inihain ko?' => 'I-withdraw ang pending na booking, at magtanong sa opisina tungkol sa iba. Ang naitala ng opisina ay sa kanila na baguhin.',
                    ],
                ],
                [
                    'title'  => 'Mga gawi na nakakatipid ng oras',
                    'notes'  => [
                        'Maghanap sa sidebar kapag hindi mo matandaan kung saan nakatira ang isang pahina.',
                        'Panatilihing napapanahon ang iyong email address sa Profile — dito sumusulat sa iyo ang opisina.',
                        'Ikabit ang malinaw na larawan ng iyong patunay ng bayad at kopyahin nang eksakto ang reference nito; mas mabilis itong maaprubahan.',
                        'Tingnan ang Aking mga Bayad pagkatapos sabihin ng opisina na naaprubahan na ang resibo, upang kumpirmahing naitala ito.',
                        'Panatilihing napapanahon ang mga miyembro ng iyong sambahayan, dahil binabasa ng opisina ang mga ito para sa kanilang mga rekord.',
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
                        'I-acknowledge'        => 'Markahan ang paglabag bilang nabasa. Resibo ito, hindi pag-amin.',
                        'Amenity'              => 'Isang pinagsasaluhang pasilidad na maaari mong ireserba, tulad ng clubhouse o court.',
                        'Bill'                 => 'Isang singil na kailangan mong bayaran, may sariling reference.',
                        'Booking'              => 'Isang reserbasyon ng amenity para sa isang petsa at hanay ng oras.',
                        'Reklamo (Complaint)'  => 'Isang alalahanin na inihain mo sa opisina.',
                        'Deposit'              => 'Perang hawak laban sa isang booking at ibinabalik o iforfeit pagkatapos.',
                        'Dues'                 => 'Ang paulit-ulit na singil na ipinapataw sa bawat sambahayan.',
                        'Sambahayan'           => 'Ang mga taong nakatira sa iyong unit.',
                        'Invoice'              => 'Isang bill na itinataas ng opisina nang manu-mano para sa sariling singil nito.',
                        'Miyembro'             => 'Isang taong nakatira sa iyong unit, naitala sa ilalim ng Sambahayan.',
                        'Pending'              => 'Inihain, at naghihintay sa aksyon ng opisina.',
                        'Personal na impormasyon' => 'Ang mga detalye ng resident na inaayos ng opisina para sa iyo, kung saan ipi-print ang mga dokumento.',
                        'Portal'               => 'Ang site na ito, sa /portal — ang panig ng iyong sambahayan sa mga rekord ng asosasyon.',
                        'Resibo (Receipt)'     => 'Ang naitalang pera na kinakatawan ng isang bayad.',
                        'Request'              => 'Ang aplikasyon mo para sa isang dokumento o serbisyo mula sa opisina.',
                        'Resident'             => 'Ang taong na-profile ng opisina; ang iyong account ay naka-link sa rekord na iyon.',
                        'Statement of Account' => 'Ang listahan ng mga bill na kailangan pang bayaran ng iyong sambahayan.',
                        'Paglabag (Violation)' => 'Isang naitalang paglabag sa mga tuntunin ng asosasyon.',
                    ],
                ],
            ],
        ],

    ],
];
