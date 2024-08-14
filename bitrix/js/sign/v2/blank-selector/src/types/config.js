import type { BaseEvent } from 'main.core.events';

export type BlankSelectorConfig = {
	events?: {
		[key: string]: (event: BaseEvent) => void
	},
	uploaderOptions: {
		acceptedFileTypes: [],
		maxFileSize: number,
		maxFileCount: number,
		imageMaxFileSize: number,
		maxTotalFileSize: number,
	},
	portalConfig: {
		isUnsecuredScheme: boolean,
		isDomainChanged: boolean,
		isEdoRegion: boolean,
	},
};
