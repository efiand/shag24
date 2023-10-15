import { applyAll, applyClass } from '../utils';
import Chooser from '../components/chooser';
import Field from '../components/field';
import Gallery from '../components/gallery';
import HashLink from '../components/hash-link';
import Header from '../components/header';
import PlaceFragmentChooser from '../components/place-fragment-chooser';
import Switchable from '../components/switchable';
import TelLinkSmart from '../components/tel-links-smart';
import TicketForm from '../components/ticket-form';
import UnitWrap from '../components/unit-wrap';
import YaShare from '../components/ya-share';

const hashLinks = document.querySelectorAll(`a[href^="#"]`);

applyClass(Chooser, `.chooser:not(.place-fragment-chooser)`);
applyClass(Switchable, `.switchable`);
applyClass(PlaceFragmentChooser, `.place-fragment-chooser`);
applyClass(Field, `.field`);
applyClass(TicketForm, `.form--ticket`);
applyClass(Gallery, `.gallery`);
applyClass(Header, `.header`);
applyClass(TelLinkSmart, `.tel-links-smart`);
applyClass(YaShare, `.ya-share`);
applyAll(hashLinks, (hashLink) => new HashLink(hashLink, hashLinks));

const unitWrapBlocks = document.querySelectorAll(`.unit-wrap`);
if (unitWrapBlocks.length) {
	const unitWraps = [];
	const unitNumbers = new Set(); // eslint-disable-line
	for (const unitWrapBlock of unitWrapBlocks) {
		const unitWrap = new UnitWrap(unitWrapBlock);
		const unitNumber = unitWrap.getNumber();

		unitWraps.push(unitWrap);
		if (Number.isInteger(unitNumber)) {
			unitNumbers.add(unitNumber);
		}
	}

	fetch(`/people`, {
		body: JSON.stringify(Array.from(unitNumbers)),
		method: `post`
	})
		.then((res) => res.json())
		.then((data) => {
			unitWraps.forEach((unitWrap) => {
				unitWrap.init(data.people.find((item) => Number(item.id) === unitWrap.getNumber()));
			});
		});
}
