import { createReduxStore, registerStore } from '@wordpress/data';

import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';

export const STORE_NAME = 'wnftd/nft-ownership';

const storeConfig = {
	reducer,
	selectors,
	actions,
	__experimentalUseThunks: true,
};

export const store = createReduxStore( STORE_NAME, storeConfig );

registerStore( STORE_NAME, storeConfig );
