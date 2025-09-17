export function applyReview({ ease, interval, grade }: { ease: number; interval: number; grade: 0 | 1 | 2 | 3 }) {
  const newEase = Math.max(1.3, ease + (-0.8 + 0.28 * grade - 0.02 * grade * grade));
  let newInterval = interval;
  if (grade < 2) {
    newInterval = 1;
  } else {
    newInterval = interval === 0 ? 1 : Math.round(interval * newEase);
  }
  const due = new Date();
  due.setDate(due.getDate() + newInterval);
  return { ease: newEase, interval: newInterval, dueAt: due };
}
