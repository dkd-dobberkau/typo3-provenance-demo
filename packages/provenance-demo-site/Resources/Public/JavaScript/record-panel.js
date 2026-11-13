/**
 * Fills the record panel (stamp, JSON fragment, fact list) from the page's
 * own JSON-LD provenance record, so the panel never claims more than the
 * record actually says.
 */
(() => {
  const findRecord = () => {
    for (const script of document.querySelectorAll('script[type="application/ld+json"]')) {
      try {
        const data = JSON.parse(script.textContent);
        if (data && data.cpsConformance !== undefined) {
          return data;
        }
      } catch (error) {
        console.warn('[provenance-demo-site] Unreadable JSON-LD block', error);
      }
    }
    return null;
  };

  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const formatDate = (value) => {
    const date = new Date(value);
    return Number.isNaN(date.getTime())
      ? value
      : date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
  };

  const shorten = (value, length = 22) =>
    typeof value === 'string' && value.length > length ? `${value.slice(0, length)}…` : value;

  const renderFragment = (target, entries) => {
    target.textContent = '';
    target.append(element('span', 'punct', '{'), '\n');
    entries.forEach(([key, value], index) => {
      const pending = value === undefined || value === null;
      target.append(
        '  ',
        element('span', 'key', `"${key}"`),
        element('span', 'punct', ': '),
        element('span', pending ? 'pending' : 'str', `"${pending ? 'pending' : value}"`),
        element('span', 'punct', index < entries.length - 1 ? ',' : ''),
        '\n',
      );
    });
    target.append(element('span', 'punct', '}'));
  };

  const renderFacts = (section, claims) => {
    if (!section || claims.length === 0) return;
    const list = section.querySelector('.fact-list');
    claims.forEach((claim) => {
      const item = element('li');
      const link = element('a', '', claim.text || claim.object || claim['@id']);
      const anchor = (claim['@id'] || '').split('#')[1];
      if (anchor) link.href = `#${anchor}`;
      if (claim.object && claim.object !== claim.text) link.title = claim.object;
      item.append(link, element('span', `confidence ${claim.confidence || ''}`, claim.confidence || ''));
      list.append(item);
    });
    section.hidden = false;
  };

  const record = findRecord();
  const set = (selector, text) => {
    const node = document.querySelector(selector);
    if (node) node.textContent = text;
  };

  if (!record) {
    renderFragment(document.querySelector('[data-cps-fragment]'), [['provenance', null]]);
    return;
  }

  const signed = Boolean(record.signature && record.signature.signatureValue);
  document.body.classList.toggle('is-signed', signed);

  set('[data-cps-status]', signed ? 'Signed record' : 'Unsigned record');
  set('[data-cps-modified]', `Revised ${formatDate(record.lastSubstantiveRevision || record.dateModified)}`);
  set('[data-cps-stamp-top]', signed ? 'Signed' : 'Recorded');
  set('[data-cps-stamp-level]', `L${record.cpsConformance}`);
  set('[data-cps-stamp-bottom]', signed ? record.signature.algorithm : 'no key yet');

  renderFragment(document.querySelector('[data-cps-fragment]'), [
    ['@type', record['@type']],
    ['headline', record.headline],
    ['datePublished', record.datePublished],
    ['dateModified', record.dateModified],
    ['contentHash', shorten(record.contentHash)],
    ['keyId', signed ? record.signature.keyId : null],
    ['signature', signed ? shorten(record.signature.signatureValue, 16) : null],
  ]);

  const claims = Array.isArray(record.claim) ? record.claim : record.claim ? [record.claim] : [];
  renderFacts(document.querySelector('[data-cps-facts]'), claims);
})();
