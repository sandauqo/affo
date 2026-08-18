class GoodMatrix {
	constructor(info) {
        this.info = info;
        this.matrix = this.getMatrix(info);
        this.paddedMatrix = this.getPaddedMatrix();
        this.initPosition = this.getStartingPosition();
        this.position = this.initPosition;
        this.direction = DIRECTION.UP
        this.steps = [];
	}

    getMatrix(info) {
		const row = new Array(info.rows).fill(1);
		let matrix = row.map(i => new Array(info.cols).fill(1));
// console.log(matrix);
// console.log(info.rows);
// console.log(info.cells)
		// get default orientation
		for (const cell of Object.values(info.cells)) {
			if (cell.type == "void") {
				// count from bottom
			    matrix[(info.rows - 1 - cell.coords[1])][cell.coords[0]] = 0;
			}
		}

		// adjust if flipped
		if (info.flipped) {
			//transpose
			//reverse
			//transpose again
			matrix = this.transpose(this.transpose(matrix).reverse());
		}

		// adjust if rotated
		for (let i = 0; i < +info.rotation; i++) {
			matrix = this.rotate(matrix);
		}

		return matrix;
	}

    getPaddedMatrix() {
        const zeroes = new Array(this.matrix[0].length).fill(0);
        const matrix = JSON.parse(JSON.stringify(this.matrix));
        matrix.unshift(zeroes);
        matrix.push(zeroes);
        for (let i = 0; i < matrix.length; i++) {
            matrix[i] = [0, ...matrix[i], 0];
        }

        return matrix;
    }

    getStartingPosition() {
        const offset = this.matrix[0].findIndex(i => i == 1);
        // #inc by 1 to account for padded matrix
        return [1, offset + 1]
    }

	transpose(array) {
		return array[0].map((col, i) => array.map(row => row[i]));
	}

	rotate(array) {
		return this.transpose(array.reverse());
	}

    walkAround() {
        const q = [this.quadrant(1), this.quadrant(2), this.quadrant(3), this.quadrant(4)];
        if (this.direction == DIRECTION.RIGHT){
            if (this.matches([0, 0, 1, 1], q)) {
                return this.takeStep(DIRECTION.RIGHT);
            }
            if (this.matches([0, 0, 1, 0], q)) {
                return this.takeStep(DIRECTION.DOWN);
            }
            if (this.matches([1, 0, 1, 1], q)) {
                return this.takeStep(DIRECTION.UP);
            }
        }
        if (this.direction == DIRECTION.DOWN){
            if (this.matches([0, 1, 1, 1], q)) {
                return this.takeStep(DIRECTION.RIGHT);
            }
            if (this.matches([0, 1, 0, 0], q)) {
                return this.takeStep(DIRECTION.LEFT);
            }
            if (this.matches([0, 1, 1, 0], q)) {
                return this.takeStep(DIRECTION.DOWN);
            }
        }
        if (this.direction == DIRECTION.LEFT){
            if (this.matches([1, 1, 0, 0], q)) {
                return this.takeStep(DIRECTION.LEFT);
            }
            if (this.matches([1, 1, 1, 0], q)) {
                return this.takeStep(DIRECTION.DOWN);
            }
            if (this.matches([1, 0, 0, 0], q)) {
                return this.takeStep(DIRECTION.UP);
            }
        }
        if (this.direction == DIRECTION.UP){
            if (this.matches([0, 0, 0, 1], q)) {
                return this.takeStep(DIRECTION.RIGHT);
            }
            if (this.matches([0, 0, 1, 0], q)) {
                return this.takeStep(DIRECTION.LEFT);
            }
            if (this.matches([1, 0, 0, 1], q)) {
                return this.takeStep(DIRECTION.UP);
            }
        }
    }

    generateClipPath() {
        let iter = 0;
        while (iter < 100) {
            if (JSON.stringify(this.initPosition) == JSON.stringify(this.position) && iter > 0) {
                break;
            }
            this.walkAround();
            iter++;
        }

        return this.assembleClipPath();
    }

    quadrant(n) {
        if (n == 1) {
            return this.paddedMatrix[this.position[0] - 1][this.position[1]];
        }
        if (n == 2) {
            return this.paddedMatrix[this.position[0] - 1][this.position[1] - 1];
        }
        if (n == 3) {
            return this.paddedMatrix[this.position[0]][this.position[1]-1];
        }
        if (n == 4) {
            return this.paddedMatrix[this.position[0]][this.position[1]];
        }
    }

    takeStep(dir) {
        this.direction = dir;
        this.steps.push(dir);
        if (dir == DIRECTION.RIGHT) {
            this.position = [this.position[0], this.position[1]+1];
        }
        if (dir == DIRECTION.DOWN) {
            this.position = [this.position[0]+1, this.position[1]]
        }
        if (dir == DIRECTION.LEFT) {
            this.position = [this.position[0], this.position[1]-1]
        }
        if (dir == DIRECTION.UP) {
            this.position = [this.position[0]-1, this.position[1]]
        }
    }

    matches (arr1, arr2) {
        return JSON.stringify(arr1) == JSON.stringify(arr2) || JSON.stringify(arr1) == JSON.stringify(this.invert(arr2));
    }

    invert(arr) {
        return arr.map(e => 1 - e);
    }

    assembleClipPath() {
        const hStep = 100 / this.matrix[0].length;
        const vStep = 100 / this.matrix.length;
        const percents = [[0, this.matrix[0].findIndex(i => i == 1) * hStep]];

        this.steps.forEach(step => {
            if (step == DIRECTION.RIGHT) {
                percents.push([percents.slice(-1)[0][0], this.round(percents.slice(-1)[0][1] + hStep)]);
            }
            if (step == DIRECTION.DOWN) {
                percents.push([this.round(percents.slice(-1)[0][0] + vStep), percents.slice(-1)[0][1]]);
            }
            if (step == DIRECTION.LEFT) {
                percents.push([percents.slice(-1)[0][0], this.round(percents.slice(-1)[0][1] - hStep)]);
            }
            if (step == DIRECTION.UP) {
                percents.push([this.round(percents.slice(-1)[0][0] - vStep), percents.slice(-1)[0][1]]);
            }
        });

        let path = 'polygon('
        percents.forEach(p => {
            path += p[1] + '% ' + p[0] + '%, ';
        });
        path = path.substring(0, path.length - 2);
        path += ')';

        return path;
    }

    round(num) {
        return Math.round((num + Number.EPSILON) * 10000) / 10000;
    }

}

const DIRECTION = {
    RIGHT: "RIGHT",
    DOWN: "DOWN",
    LEFT: "LEFT",
    UP: "UP",
  };
