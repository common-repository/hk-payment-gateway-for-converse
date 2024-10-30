<?php

$bankErrorCodesByDiffLanguageConverse = [
    'hy' => [
        '-20010' => 'Գործարքը մերժվել է, քանի որ վճարումը գերազանցել է թողարկող բանկի կողմից սահմանված սահմանաչափը',
        '-9000' => 'Գործարքի սկզբի կարգավիճակ:',
        '-2019' => 'Թողարկողի PARes պարունակում է iReq, ինչի արդյունքում վճարումը մերժվել է:',
        '-2018' => 'Directory server Visa կամ MasterCard հասանելի չեն, կամ քարտի ներգավվածության հարցման(VeReq) պատասխանը չի ստացվել կապի խափանման պատճառով: Նշված սխալը հանդիսանում է վճարային ուղեմուտի և միջազգային վճարային համակարգի սերվերների փոխգործունեության արդյունքը՝ վերջիններիս տեխնիկական խափանումների պատճառով:',
        '-2016' => 'Նշանակում է որ թողարկող բանկը (տվյալ պահին) պատրաստ չէ իրականացնել գործարքի 3ds (օրինակ՝ չի աշխատում բանկի ACS-ն) և մենք չենք կարող ստուգել արդյոք քարտը ներգրավված է 3d secure-ում, թե ոչ:',
        '-2015' => 'DS-ի VERes-ը պարունակում է iReq, որի հետևանքով վճարումը մերժվել է:',
        '-2013' => 'Վճարման փորձերը սպառվել են:',
        '-2012' => 'Նշված գործարքը չի սպասարկվում համակարգի կողմից:',
        '-2011' => 'Քարտը դիտարկվել է որպես 3d secure քարտում նեգրավված, բայց թողարկող բանկը (տվյալ պահին) պատրաստ չէ իրականացնել գործարքի 3ds:',
        '2007' => 'Սպառվել է վճարման գրանցման պահից սկսած քարտի տվյալների մուտքագրման համար սահմանված ժամկետը (նշված պահին թայմաութը տեղի կունենա 20 րոպեից):',
        '2006' => 'Թողարկողը մերժել է նույնականացումը:',
        '-2005' => 'Մենք չենք կարողացել ստուգել թողարկողի ստորագրությունը, այսինքն PARes-ն ընթեռնելի էր, բայց սխալ էր ստորագրված:',
        '2004' => 'Արգելվում է SSL –ի միջոցով առանց SVС մուտքագրման վճարում իրականացնել:',
        '2002' => 'Գործարքը մերժվել է, քանի որ վճարումը գերազանցել է սահմանված սահմանաչափը:  Ծանոթագրություն. հաշվի են առնվում կամ էքվայեր բանկի կողմից առևտրային կետի օրական շրջանառության համար սահմանված սահմանաչափերը կամ առևտրային կետի՝ մեկ քարտով շրջանառության սահմանաչափը կամ էլ առևտրային կետի՝ մեկ գործառնության գծով սահմանաչափը:',
        '2001' => 'Գործարքը մերժվել է, քանի որ հաճախորդի IP հասցեն գրանցված է սև ցուցակում:',
        '2000' => 'Գործարքը մերժվել է, քանի որ քարտը գրանցված է սև ցուցակում: ',
        '-100' => 'Վճարման փորձեր չեն եղել:',
        '-1' => 'Սպառվել է պրոցեսինգային կետրոնի պատասխանին սպասելու ժամանակը:',
        '1' => 'Նշված համարով պատվերն արդեն գրանցված է համակարգում:',
        '5' => 'Հարցման պարամետրի նշանակության սխալ:',
        '100' => 'Թողարկող բանկն արգելել է քարտով առցանց գործարքների իրականացումը:',
        '101' => 'Քարտի գործողության ժամկետը սպառվել է:',
        '103' => 'Թողարկող բանկի հետ կապը բացակայում է, առևտրային կետը պետք է կապ հաստատի թողարկող բանկի հետ:',
        '104' => 'Սահմանափակումների ենթարկված հաշվով գործարք կատարելու փորձ:',
        '107' => 'Անհրաժեշտ է դիմել թողարկող բանկին:',
        '109' => 'Առևտրային կետի/տերմինալի նույնականացուցիչը սխալ է նշված  (ավարտի կամ տարբեր նույնականացուցիչներով նախնական հավաստագրման համար):',
        '110' => 'Գործարքի գումարը սխալ է նշված:',
        '111' => 'Քարտի համարը սխալ է:',
        '116' => 'Գործարքի գումարը գերազանցում է ընտրված հաշվին առկա միջոցների հասանելի մնացորդը:',
        '120' => 'Գործառնության մերժում. գործարքն արգելված է թողարկողի կողմից: Վճարային ցանցի պատասխանի կոդ՝ 57: Մերժման պատճառներն անհրաժեշտ է ճշտել թողարկողից:',
        '121' => 'Ձեռնարկվել է թողարկող բանկի կողմից սահմանված օրական սահմանաչափը գերազանցող գումարով գործարքի իրականացման փորձ:',
        '123' => 'Գերազանցվել է գործարքների թվի սահմանաչափը: Հաճախորդը կատարել է սահմանաչափի շրջանակներում թույլատրելի առավելագույն թվով գործարքները և փորձում է կատարել ևս մեկը:',
        '125' => 'Քարտի համարը սխալ է: Սառեցված գումարը գերազանցող գումարի վերադարձման փորձ, զրոյական գումարի վերադարձման փորձ:',
        '208' => 'Քարտը համարվում է կորած: ',
        '209' => 'Գերազանցվել են քարտի համար սահմանված սահմանափակումները:',
        '902' => 'Քարտապանը փորձում է կատարել իր համար արգելված գործարք:',
        '903' => 'Ձեռնարկվել է թողարկող բանկի կողմից սահմանված սահմանաչափը գերազանցող գործարքի փորձ:',
        '904' => 'Թողարկող բանկի տեսանկյունից հաղորդագրության սխալ ձևաչափ:',
        '907' => 'Տվյալ քարտը թողարկած բանկի հետ կապ հաստատված չէ: Քարտի տվյալ համարի համար stand-in ռեժիմով հավաստագրում չի թույլատրվում (այսինքն  թողարկողը չի կարող կապ հաստատել վճարային ցանցի հետ, այդ պատճառով գործարքը հնարավոր է իրականացնել միայն offline ռեժիմով՝ այնուհետև փոխանցել Back Office-ին, այլապես գործարքը մերժվում է):',
        '909' => 'Համակարգի գործունեության ընդհանուր բնույթի սխալ, որը հայտնաբերվում է վճարային ցանցի կամ թողարկող բանկի կողմից:',
        '910' => 'Թողարկող բանկն անհասանելի է:',
        '913' => 'Ցանցի տեսանկյունից սխալ ձևաչափ:',
        '914' => 'Գործարքը չի գտնվել (երբ ուղարկվում է ավարտ, reversal կամ refund):',
        '999' => 'Բացակայում է գործարքի հավաստագրման սկիզբը: Մերժվել է զեղծարարության կամ 3dsec. սխալի պատճառով:',
        '1001' => 'Տրվում է գործարքի գրանցման պահին, այսինքն այն պահին, երբ քարտի տվյալները դեռևս չեն ներմուծվել:',
        '2003' => 'SSL (Не 3d-Secure/SecureCode) գործարքներն արգելված են տվյալ առևտրային կետի համար:',
        '2005' => 'Վճարումը չի համապատասխանում 3ds ստուգման կանոնների պահանջներին:',
        '8204' => 'Նման պատվեր արդեն գրանցվել է (ստուգում ըստ ordernumber-ի):',
        '9001' => 'RBS մերժման ներքին կոդ',
        '1015' => 'Ներմուծվել են քարտի սխալ պարամետրեր:',
        '1017' => '3-D Secure – կապի խափանում',
        '1018' => 'Թայմաուտ պրոցեսինգի ընթացքում: Չի հաջողվել ուղարկել:',
        '1019' => 'Թայմաուտ պրոցեսինգի ընթացքում: Հաջողվել է ուղարկել, բայց բանկից պատասխան չի ստացվել:',
        '1014' => 'RBS մերժման կոդ',
        '1041' => 'Դրամական միջոցների վերադարձման սխալ:',
    ],
    'ru' => [
        '-20010' => 'Транзакция отклонена по причине того, что размер платежа превысил установленные лимиты Банком-эмитентом',
        '-9000' => 'Состояние начала транзакции',
        '-2019' => 'PARes от эмитента содержит iReq, вследствие чего платеж был отклонен',
        '-2018' => 'Directory server Visa или MasterCard либо недоступен, либо в ответ на запрос вовлеченности карты (VeReq) пришла ошибка связи. Это ошибка взаимодействия платежного шлюза и серверов МПСпо причине технических неполадок на стороне последних',
        '-2016' => 'Это означает, что банк- эммитент не готов (в данный момент времени) провести 3ds транзакцию (например, не работает ACS банка). Мы не можем определить вовлечена ли карта в 3d secure',
        '-2015' => 'VERes от DS содержит iReq, вследствие чего платеж был отклонен',
        '-2013' => 'Исчерпаны попытки оплаты',
        '-2012' => 'Данная операция не поддерживается',
        '-2011' => 'Карта определена как вовлеченная в карта 3d secure, но банк- эммитент не готов (в данный момент времени) провести 3dsтранзакцию',
        '2007' => 'Истек срок, отведенный на ввод данных карты с момента регистрации платежа (в данный момент таймаут наступит через 20 минут)',
        '2006' => 'Означает, что эмитент отклонил аутентификацию',
        '-2005' => 'Означает, что мы не смогли проверить подпись эмитента, то есть PARes был читаемый, но подписан неверно.',
        '2004' => 'Оплата через SSL без ввода SVС запрещена',
        '2002' => 'Транзакция отклонена по причине того, что размер платежа превысил установленные лимиты. Примечание: имеется в виду либо лимиты Банка-эквайера на дневной оборот Магазина, либо лимиты Магазина на оборот по одной карте, либо лимит Магазина по одной операции)', '2001' => 'Транзакция отклонена по причине того, что IP-адрес Клиента внесен в черный список.',
        '2000' => 'Транзакция отклонена по причине того, что карта внесена в черный список',
        '-100' => 'Не было попыток оплаты.',
        '-1' => 'Истекло время ожидания ответа от процессинговой системы.',
        '1' => 'Для успешного завершения транзакции,требуется подтверждение личности. В случае интернет-транзакции (соот-но и в нашем) невозможно, поэтому считается как declined.',
        '5' => 'Отказ сети проводить транзакцию.',
        '100' => 'Банк эмитент запретил интернеттранзакции по карте.',
        '101' => 'Истек срок действия карты.',
        '103' => 'Нет связи с Банком-Эмитентом.Торговой точке необходимо связаться с банком-эмитентом.',
        '104' => 'Попытка выполнения операции по счету, на использование которого наложены ограничения.',
        '107' => 'Следует обратиться к Банку-Эмитенту.',
        '109' => 'Неверно указан идентификатор мерчанта/терминала (для завершения и предавторизация с разнымиидентификаторами)',
        '110' => 'Неверно указана сумма транзакции',
        '111' => 'Неверный номер карты',
        '116' => 'Сумма транзакции превышает доступный остаток средств на выбранном счете.',
        '120' => 'Отказ в проведении операции - транзакция не разрешена эмитентом. Код ответа платежной сети - 57. Причины отказа необходимо уточнять у эмитента.',
        '121' => 'Предпринята попытка выполнить транзакцию на сумму, превышающую дневной лимит, заданный банком-эмитентом',
        '123' => 'Превышен лимит на число транзакций: клиент выполнил максимально разрешенное число транзакций в течение лимитного цикла и пытается провести еще одну.',
        '125' => 'Неверный номер карты. Попытка возврата на сумму, больше холда, попытка возврата нулевой суммы.',
        '208' => 'Карта утеряна',
        '209' => 'Превышены ограничения по карте',
        '902' => 'Владелец карты пытается выполнить транзакцию,которая для него не разрешена.',
        '903' => 'Предпринята попытка выполнить транзакцию на сумму, превышающую лимит, заданный банком-эмитентом',
        '904' => 'Ошибочный формат сообщения с точки зрения банка эмитента.',
        '907' => 'Нет связи с Банком, выпустившим Вашу карту. Для данного номера карты не разрешена авторизация в режиме stand-in (этот режим означает, что эмитент не может связаться с платежной сетью и поэтому транзакция возможна либо в оффлайне с последующей выгрузкой в бэк офис, либо она будет отклонена)',
        '909' => 'Ошибка функционирования системы, имеющая общий характер. Фиксируется платежной сетью или банком-эмитентом.',
        '910' => 'Банк-эмитент недоступен.',
        '913' => 'Неправильный форматтранзакции с точки зрения сети.',
        '914' => 'Не найдена транзакция (когда посылается завершение или reversal или refund)',
        '999' => 'Отсутствует начало авторизации транзакции. Отклонено по фроду или ошибка 3dsec.',
        '1001' => 'Выставляется в момент регистрации транзакции,т.е. когда еще по транзакции не было введено данных карты',
        '200' => 'Фродовая транзакция (по мнению процессинга или платежной сети)',
        '2003' => 'SSL (Не 3d-Secure/SecureCode)транзакции запрещены Магазину',
        '2005' => 'Платеж не соотвествует условиям правила проверки по 3ds',
        '8204' => 'Такой заказ уже регистрировали (проверка по ordernumber)',
        '9001' => 'Внутренний код отказа РБС',
        '1015' => 'Введены неправильные параметры карты',
        '1017' => '3-D Secure - ошибка связи',
        '1018' => 'Таймаут в процессинге. Не удалось отправить',
        '1019' => 'Таймаут в процессинге. Удалось отправить, но не получен ответ от банка',
        '1014' => 'Код отказа РБС',
        '1041' => 'Ошибка возврата денежных средств',
    ],
    'en' => [
        '-20010' => 'BLOCKED_BY_LIMIT',
        '-9000' => 'Started',
        '-2019' => 'Decline by iReq in PARes',
        '-2018' => 'Declined. DS connection timeout',
        '-2016' => 'Declined. VeRes status is unknown',
        '-2015' => 'Decline by iReq in VERes',
        '-2013' => 'Исчерпаны попытки оплаты',
        '-2012' => 'Operation not supported',
        '-2011' => 'Declined. PaRes status is unknown',
        '2007' => 'Decline. Payment time limit',
        '2006' => 'Decline. 3DSec decline',
        '-2005' => 'Decline. 3DSec sign error',
        '2004' => 'SSL without CVC forbidden',
        '2002' => 'Decline. Payment over limit',
        '2001' => 'Decline. IP blacklisted',
        '2000' => 'Decline. PAN blacklisted',
        '-100' => 'no_payments_yet',
        '-1' => 'sv_unavailable',
        '1' => 'Declined. Honor with id',
        '5' => 'Decline. Unable to process',
        '100' => 'Decline. Card declined',
        '101' => 'Decline. Expired card',
        '103' => 'Decline. Call issuer',
        '104' => 'Decline. Card declined',
        '107' => 'Decline. Call issuer',
        '109' => 'Decline. Invalid merchant',
        '110' => 'Decline. Invalid amount',
        '111' => 'Decline. No card record на Decline. Wrong PAN',
        '116' => 'Decline. Decline. Not enough money',
        '120' => 'Decline. Not allowed ',
        '121' => 'Decline. Excds wdrwl limt',
        '123' => 'Decline. Excds wdrwl ltmt',
        '125' => 'Decline. Card declined',
        '208' => 'Decline. Card is lost',
        '209' => 'Decline. Card limitations exceeded',
        '902' => 'Decline. Invalid trans',
        '903' => 'Decline. Re-enter trans.',
        '904' => 'Decline. Format error',
        '907' => 'Decline. Host not avail.',
        '909' => 'Decline. Call issuer',
        '910' => 'Decline. Host not avail.',
        '913' => 'Decline. Invalid trans',
        '914' => 'Decline. Orig trans not found',
        '999' => 'Declined by fraud',
        '1001' => 'Decline. Data input timeout',
        '200' => 'Decline. Fraud',
        '2003' => 'Decline. SSL restricted',
        '2005' => '3DS rule failed',
        '8204' => 'Decline. Duplicate order',
        '9001' => 'RBS internal error',
        '1015' => 'Decline. Input error',
        '1017' => 'Decline. 3DSec comm error',
        '1018' => 'Decline. Processing timeout',
        '1019' => 'Decline. Processing timeout',
        '1014' => 'Decline. General Error',
        '1041' => 'Decline. Refund failed',
    ]
];