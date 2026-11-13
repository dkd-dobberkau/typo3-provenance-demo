<?php

declare(strict_types=1);

/**
 * Demo content: a short history of collecting. Checked against the cited
 * Wikipedia articles on 2026-11-13.
 *
 * Every fact quote must appear verbatim, inside a single text node, in the
 * bodytext of its content element. "{chapter-key}" in the home page text is
 * replaced with a link to that chapter.
 */

$wiki = 'https://en.wikipedia.org/wiki/';

return [
    'home' => [
        'page' => [
            'title' => 'A Short History of Collecting',
            'nav_title' => 'Home',
            'subtitle' => 'Objects, labels and the records that tell us where things come from.',
        ],
        'content' => [
            [
                'header' => 'Labels, records, claims',
                'bodytext' => '<p>People have collected objects for as long as there have been objects worth keeping. Long before the word museum existed, collectors arranged, labelled and described what they owned, and every label made a claim about where a thing came from.</p>'
                    . '<p>This site follows that history in four steps: from <a href="{the-oldest-museum}">a princess at Ur</a>, through the <a href="{cabinets-of-curiosity}">cabinets of curiosity</a> of early modern Europe and <a href="{public-museum}">the first public museums</a>, to the <a href="{biography-of-an-object}">provenance research</a> that museums carry out today.</p>'
                    . '<p>Every page is itself a provenance record. It is signed with an Ed25519 key, and individual statements are marked as facts with a source and a degree of confidence. The panel next to the text shows what the record says.</p>',
                'facts' => [
                    [
                        'fact_id' => 'ennigaldi-princess',
                        'quote' => 'a princess at Ur',
                        'subject' => $wiki . 'Ennigaldi-Nanna',
                        'predicate' => 'description',
                        'object_literal' => 'Daughter of Nabonidus and high priestess of the moon god at Ur',
                        'source_url' => $wiki . 'Ennigaldi-Nanna',
                        'confidence' => 'asserted',
                    ],
                ],
            ],
        ],
    ],
    'chapters' => [
        'the-oldest-museum' => [
            'page' => [
                'title' => 'The Oldest Museum',
                'subtitle' => 'A princess at Ur collected objects that were already ancient in her own time.',
            ],
            'content' => [
                [
                    'header' => 'A collection at Ur',
                    'bodytext' => '<p>Around 530 BCE, Ennigaldi-Nanna, a daughter of Nabonidus, the last king of the Neo-Babylonian Empire, gathered objects from the past at Ur, in the south of present-day Iraq. Her father had made her high priestess of the moon god Nanna there.</p>'
                        . '<p>When the archaeologist Leonard Woolley excavated the palace and temple complex in 1925, he found artefacts lying neatly side by side, although their ages varied by centuries.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'ur-museum-date',
                            'quote' => 'Around 530 BCE',
                            'subject' => $wiki . "Ennigaldi-Nanna's_museum",
                            'predicate' => 'foundingDate',
                            'object_literal' => 'c. 530 BCE',
                            'source_url' => $wiki . "Ennigaldi-Nanna's_museum",
                            'confidence' => 'estimated',
                        ],
                        [
                            'fact_id' => 'ennigaldi-parent',
                            'quote' => 'a daughter of Nabonidus',
                            'subject' => $wiki . 'Ennigaldi-Nanna',
                            'predicate' => 'parent',
                            'object_uri' => $wiki . 'Nabonidus',
                            'source_url' => $wiki . 'Ennigaldi-Nanna',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
                [
                    'header' => 'The first labels',
                    'bodytext' => '<p>Next to the objects lay clay drums inscribed in three languages, among them Sumerian, that described them. Woolley read them as labels, and the collection is now considered the earliest known public museum.</p>'
                        . '<p>The labels did what a provenance record does today: they told a visitor what an object was and where it came from. They were written long after the objects themselves, by someone who had to rely on older sources.</p>',
                ],
            ],
        ],
        'cabinets-of-curiosity' => [
            'page' => [
                'title' => 'Cabinets of Curiosity',
                'subtitle' => 'Rooms full of wonders, collected by princes, physicians and scholars.',
            ],
            'content' => [
                [
                    'header' => 'A room for everything remarkable',
                    'bodytext' => '<p>From the sixteenth century onwards, princes, physicians and scholars across Europe filled rooms with whatever seemed remarkable: shells, minerals, coins, instruments, carvings and objects from distant countries. In German these rooms were called Wunderkammer, wonder-rooms, or Kunstkammer, art collections.</p>'
                        . '<p>Things were prized for being wondrously old, wondrously fine or wondrously small. Today we would sort the same contents into natural history, geology, ethnography and archaeology.</p>',
                ],
                [
                    'header' => 'Ambras Castle',
                    'bodytext' => '<p>Archduke Ferdinand II of Austria, one of the great collectors of his time, housed his collections at Ambras Castle in the hills above Innsbruck. Its Chamber of Art and Wonders is the only Renaissance Kunstkammer that can still be seen in its original place.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'ambras-location',
                            'quote' => 'in the hills above Innsbruck',
                            'subject' => $wiki . 'Ambras_Castle',
                            'predicate' => 'containedInPlace',
                            'object_uri' => $wiki . 'Innsbruck',
                            'source_url' => $wiki . 'Ambras_Castle',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
                [
                    'header' => 'Museum Wormianum',
                    'bodytext' => '<p>The Danish physician Ole Worm kept his collection in Copenhagen. He died there in 1654 of the plague, having stayed in the city to treat the sick. A catalogue of his collection, the Museum Wormianum, was published in 1655 in Leiden.</p>'
                        . '<p>Its engraved frontispiece shows stuffed animals hanging from the ceiling and shelves of labelled boxes. It became the best-known picture of a cabinet of curiosities.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'museum-wormianum-published',
                            'quote' => 'published in 1655',
                            'subject' => $wiki . 'Museum_Wormianum',
                            'predicate' => 'datePublished',
                            'object_literal' => '1655',
                            'source_url' => $wiki . 'Museum_Wormianum',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
            ],
        ],
        'public-museum' => [
            'page' => [
                'title' => 'From Cabinet to Public Museum',
                'subtitle' => 'How private collections became institutions that belong to everyone.',
            ],
            'content' => [
                [
                    'header' => 'The Ashmolean',
                    'bodytext' => '<p>John Tradescant the elder and his son of the same name kept a collection in Lambeth, London, that drew many visitors. It became known as the Ark.</p>'
                        . '<p>The younger Tradescant left it to Elias Ashmole, who gave it to the University of Oxford in 1677. The Ashmolean Museum opened in 1683 and is considered Britain&#039;s first public museum.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'ashmolean-opened',
                            'quote' => 'opened in 1683',
                            'subject' => $wiki . 'Ashmolean_Museum',
                            'predicate' => 'foundingDate',
                            'object_literal' => '1683',
                            'source_url' => $wiki . 'Ashmolean_Museum',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
                [
                    'header' => 'The British Museum',
                    'bodytext' => '<p>When Sir Hans Sloane died in 1753, he left his collection of around 71,000 objects to the nation in return for a payment of 20,000 pounds to his heirs, far less than it was worth. An Act of Parliament founded the British Museum the same year.</p>'
                        . '<p>Its first galleries and a reading room for scholars opened on 15 January 1759.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'sloane-collection-size',
                            'quote' => 'around 71,000 objects',
                            'subject' => $wiki . 'Hans_Sloane',
                            'predicate' => 'description',
                            'object_literal' => 'Bequeathed a collection of around 71,000 objects',
                            'source_url' => $wiki . 'British_Museum',
                            'confidence' => 'estimated',
                        ],
                    ],
                ],
                [
                    'header' => 'The Louvre',
                    'bodytext' => '<p>In August 1793, during the French Revolution, the former royal palace of the Louvre opened as a public museum, on the first anniversary of the fall of the monarchy. Three quarters of the works on show came from the royal collections, the rest from the property of émigrés and the Church.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'louvre-opened',
                            'quote' => 'In August 1793',
                            'subject' => $wiki . 'Louvre',
                            'predicate' => 'foundingDate',
                            'object_literal' => '1793-08-10',
                            'source_url' => $wiki . 'Louvre',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
            ],
        ],
        'biography-of-an-object' => [
            'page' => [
                'title' => 'The Biography of an Object',
                'subtitle' => 'Provenance: who made it, who owned it, and how we know.',
            ],
            'content' => [
                [
                    'header' => 'Where things come from',
                    'bodytext' => '<p>The word provenance comes from the French provenir, to come from. In collections it means the chronology of an object&#039;s ownership, custody and location: who made it, who owned it, how it changed hands and where it was kept.</p>'
                        . '<p>A complete provenance is rare. Gaps are normal. What matters is that they are visible and that every statement can be traced to a source.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'provenance-etymology',
                            'quote' => 'the French provenir',
                            'subject' => $wiki . 'Provenance',
                            'predicate' => 'description',
                            'object_literal' => 'From French provenir, to come from',
                            'source_url' => $wiki . 'Provenance',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
                [
                    'header' => 'Provenance research',
                    'bodytext' => '<p>The question became urgent after the Second World War. In December 1998, a conference in Washington attended by 44 countries presented the Washington Conference Principles on Nazi-Confiscated Art. They are not legally binding.</p>'
                        . '<p>The principles ask that confiscated art be identified, that records be opened to researchers and that the art be made public, so that pre-war owners and their heirs can come forward. Where they are found, the aim is a just and fair solution.</p>',
                    'facts' => [
                        [
                            'fact_id' => 'washington-principles',
                            'quote' => 'attended by 44 countries',
                            'subject' => $wiki . 'Washington_Conference_Principles_on_Nazi-Confiscated_Art',
                            'predicate' => 'description',
                            'object_literal' => 'Presented in December 1998 at a conference attended by 44 countries',
                            'source_url' => $wiki . 'Washington_Conference_Principles_on_Nazi-Confiscated_Art',
                            'confidence' => 'asserted',
                        ],
                    ],
                ],
                [
                    'header' => 'And on the web',
                    'bodytext' => '<p>Web content has the same problem in a new form. A page has an author, a date, revisions and sources, but readers rarely see any of it, and nothing proves that it has not been changed.</p>'
                        . '<p>The Content Provenance Specification gives web pages what a good museum label gives an object: a record of origin that anyone can check.</p>',
                ],
            ],
        ],
    ],
];
